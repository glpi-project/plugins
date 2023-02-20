<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model {
   protected $table = 'plugin';
   public $timestamps = false;
   protected $casts = [
     "note" => 'float'
   ];

   // Relations

   public function descriptions() {
      return $this->hasMany('\API\Model\PluginDescription');
   }

   public function authors() {
      return $this->belongsToMany('\API\Model\Author', 'plugin_author');
   }

   public function stars() {
      return $this->hasMany('\API\Model\PluginStar');
   }

   public function downloads() {
      return $this->hasMany('\API\Model\PluginDownload');
   }

   public function screenshots() {
      return $this->hasMany('\API\Model\PluginScreenshot');
   }

   public function versions() {
      return $this->hasMany('\API\Model\PluginVersion')
                  ->orderBy('compatibility', 'desc')
                  ->orderBy('num', 'desc');
   }

   public function langs() {
      return $this->belongsToMany('\API\Model\PluginLang', 'plugin_plugin_lang');
   }

   public function tags() {
      return $this->belongsToMany('\API\Model\Tag', 'plugin_tags');
   }

   public function watchers() {
      return $this->hasMany('\API\Model\PluginWatch');
   }

   public function permissions() {
      return $this->belongsToMany('\API\Model\User', 'plugin_permission', 'plugin_id')
                  ->withPivot('admin', 'allowed_refresh_xml', 'allowed_change_xml_url', 'allowed_notifications');
   }

   // Scopes

   public function scopeShort($query) {
      $query->select(['plugin.id', 'plugin.name', 'plugin.key', 'plugin.logo_url',
                      'plugin.xml_url', 'plugin.homepage_url',
                      'plugin.download_url', 'plugin.issues_url', 'plugin.readme_url',
                      'plugin.license', 'plugin.date_added', 'plugin.date_updated',
                      'plugin.download_count', 'plugin.xml_state']);
      return $query;
   }

   public function scopeWhereAuthor($query, $author_id) {
    $query->where('plugin_author.author_id', '=', $author_id)
          ->leftJoin('plugin_author', 'plugin.id', '=', 'plugin_author.plugin_id');
    return $query;
   }

   public function scopeDescWithLang($query, $lang) {
      $query->addSelect([DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(plugin_description.short_description ORDER BY FIELD(plugin_description.lang, \'en\', '.DB::connection()->getPdo()->quote($lang).') DESC SEPARATOR \'#*#*\'), \'#*#*\',1) as short_description')])
           ->leftJoin('plugin_description', 'plugin.id', '=', 'plugin_description.plugin_id')
           ->groupBy('plugin.id');
      return $query;
   }

   public function scopeWithAverageNote($query) {
      $query->addSelect([DB::raw('IF(AVG(plugin_stars.note),AVG(plugin_stars.note),0) as note')])
           ->leftJoin('plugin_stars', 'plugin.id', '=', 'plugin_stars.plugin_id')
           ->groupBy('plugin.id');
      return $query;
   }

   public function scopeWithNumberOfVotes($query) {
      $query->addSelect([DB::raw('(SELECT COUNT(*) FROM plugin_stars where plugin_stars.plugin_id = plugin.id) as n_votes')]);
      return $query;
   }

   public function scopePopularTop($query) {
      $query->select(['plugin.id', 'plugin.name','plugin.key', 'download_count',
                  DB::raw('(SELECT COUNT(*) FROM plugin_stars where plugin_stars.plugin_id = plugin.id) as n_votes'),
                  DB::raw('(select AVG(plugin_stars.note) AS avg_note FROM plugin_stars WHERE plugin_stars.plugin_id = plugin.id) AS note')])
                ->orderBy('download_count', 'DESC')
                ->orderBy('note', 'DESC')
                ->orderBy('n_votes', 'DESC');
      return $query;
   }

   public function scopeMostFreshlyAddedPlugins($query) {
     $query->select(['plugin.id', 'plugin.name', 'plugin.date_added', 'plugin.key'])
         ->orderBy('plugin.date_added', 'DESC');
     return $query;
   }

   public function scopeTrendingTop($query) {
      $query->select(['plugin.id', 'plugin.name', 'plugin.key', 'plugin.download_count',
                        DB::raw('COUNT(plugin.id) as recent_downloads')])
                ->join('plugin_download', 'plugin.id', '=', 'plugin_download.plugin_id')
                ->where('downloaded_at', '>', DB::raw('NOW() - INTERVAL 1 MONTH'))
                ->groupBy('plugin.id')
                ->orderBy('recent_downloads', 'DESC');
      return $query;
   }

   public function scopeUpdatedRecently($query) {
      $query->select(['plugin.id', 'plugin.key', 'plugin.name', 'plugin.date_updated'])
           ->orderBy('date_updated', 'DESC');
      return $query;
   }

   public function scopeWithTags($query, $tags) {
      $ids = $tags->map(function ($tag) {
         return $tag->id;
      });

      $query->join('plugin_tags', 'plugin.id', '=', 'plugin_tags.plugin_id')
         ->join('tag', 'plugin_tags.tag_id', '=', 'tag.id')
         ->whereIn('tag.id', $ids);
      return $query;
   }

   public function scopeWithGlpiVersion($query, $version) {
      $query->join('plugin_version', 'plugin.id', '=', 'plugin_version.plugin_id')
            ->where('plugin_version.compatibility', '=', $version);
      return $query;
   }

   // Methods

   /**
    * Will initialize the side fetch fails counter,
    * will not do it, if it is already done.
    */
   private function initXmlFetchFailCount() {
      if (!$this->id) {
         throw new \Exception('Calling initXmlFetchFailCount() method on a unsaved Plugin model.');
      }
      $fetchFailCounter = DB::table('plugin_xml_fetch_fails')
                            ->where('plugin_id', '=', $this->id)
                            ->first();
      if (!$fetchFailCounter) {
         DB::table('plugin_xml_fetch_fails')
           ->insert([
               'plugin_id' => $this->id,
               'n' => 1
            ]);
      }
   }

   /**
    * Will increment the fetch fails counter,
    * or initialize it if no
    */
   public function incrementXmlFetchFailCount() {
      if (!$this->id) {
         throw new \Exception('Calling incrementXmlFetchFailCount() method on a unsaved Plugin model.');
      }
      $fetchFailCounter = $this->getXmlFetchFailCount();
      $fetchFailCounter++;
      if ($fetchFailCounter == 1) {
         // we set the fetch fail counter using
         // initStuff()
         return $this->initXmlFetchFailCount();
      } else {
         DB::table('plugin_xml_fetch_fails')
           ->where('plugin_id', '=', $this->id)
           ->increment('n');
      }
      // another costy query here.
      return $this->getXmlFetchFailCount();
   }

   /**
    * Will return the current fetch fails count
    */
   public function getXmlFetchFailCount() {
      if (!$this->id) {
         throw new \Exception('Calling getXmlFetchFailCount() method on a unsaved Plugin model.');
      }
      $fetchFailCounter = DB::table('plugin_xml_fetch_fails')
                            ->where('plugin_id', '=', $this->id)
                            ->first();
      if (!$fetchFailCounter) {
         return 0;
      } else {
         return (int)$fetchFailCounter->n;
      }
   }

   /**
    * Will "reset" the fails counter
    * (which in fact mean deleting the table entry)
    */
   public function resetXmlFetchFailCount() {
      if (!$this->id) {
         throw new \Exception('Calling clearXmlFetchFailCount() method on a unsaved Plugin model.');
      }
      DB::table('plugin_xml_fetch_fails')
        ->where('plugin_id', '=', $this->id)
        ->delete();
      return 0;
   }

   public function getLatestVersion() {
      $sorted = $this->versions->sortByDesc('num', SORT_NATURAL);
      return $sorted->first();
   }
}
