<?php

namespace API\Model;

use \Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model {
   protected $table = 'plugin';
   public $timestamps = false;

   protected $casts = [
     "note" => 'float'
   ];

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
      return $this->hasMany('\API\Model\PluginVersion');
   }

   public function tags() {
      return $this->belongsToMany('\API\Model\Tag', 'plugin_tags');
   }

   public function scopeShort($query) {
      $query->select(['plugin.id', 'plugin.name', 'plugin.key', 'plugin.logo_url',
                      'plugin.xml_url', 'plugin.homepage_url',
                      'plugin.download_url', 'plugin.issues_url', 'plugin.readme_url',
                      'plugin.license', 'plugin.date_added', 'plugin.date_updated']);
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

   public function scopeWithDownloads($query, $limit = false) {
      $query->addSelect([DB::raw('(SELECT COUNT(*) FROM plugin_download where plugin_download.plugin_id = plugin.id) as downloaded')])
                ->leftJoin('plugin_download', 'plugin.id', '=', 'plugin_download.plugin_id')
                ->groupBy('plugin.name');
      return $query;
   }

   public function scopeWithNumberOfVotes($query) {
      $query->addSelect([DB::raw('(SELECT COUNT(*) FROM plugin_stars where plugin_stars.plugin_id = plugin.id) as n_votes')]);
      return $query;
   }

   public function scopePopularTop($query, $limit = 10) {
      $query->select(['plugin.id', 'plugin.name','plugin.key',
                  DB::raw('(SELECT COUNT(*) FROM plugin_download where plugin_download.plugin_id = plugin.id) as downloaded'),
                  DB::raw('(SELECT COUNT(*) FROM plugin_stars where plugin_stars.plugin_id = plugin.id) as n_votes'),
                  DB::raw('(select AVG(plugin_stars.note) AS avg_note FROM plugin_stars WHERE plugin_stars.plugin_id = plugin.id) AS note')])
                ->orderBy('downloaded', 'DESC')
                ->orderBy('note', 'DESC')
                ->orderBy('n_votes', 'DESC')
                ->take(10);
      return $query;
   }

   public function scopeMostFreshlyAddedPlugins($query, $limit = 10) {
     $query->select(['plugin.id', 'plugin.name', 'plugin.date_added', 'plugin.key'])
         ->orderBy('plugin.date_added', 'DESC')
         ->take($limit);
     return $query;
   }

   public function scopeTrendingTop($query, $limit = 10) {
      $query->select(['plugin.id', 'plugin.name', 'plugin.key',
                        DB::raw('COUNT(name) as downloaded')])
                ->join('plugin_download', 'plugin.id', '=', 'plugin_download.plugin_id')
                ->where('downloaded_at', '>', DB::raw('NOW() - INTERVAL 1 MONTH'))
                ->groupBy('plugin.name')
                ->orderBy('downloaded', 'DESC')
               ->take($limit);
      return $query;
   }

   public function scopeUpdatedRecently($query, $limit = 10) {
      $query->select(['plugin.id', 'plugin.key', 'plugin.name', 'plugin.date_updated'])
           ->orderBy('date_updated', 'DESC')
           ->take($limit);
      return $query;
   }

   public function scopeWithCurrentVersion($query) {
      $query->addSelect([DB::raw('plugin_version.compatibility as compatible_with')])
           ->join('plugin_version', 'plugin.id', '=', 'plugin_version.plugin_id');
      return $query;
   }

   public function scopeWithTag($query, $tag) {
      $query->join('plugin_tags', 'plugin.id', '=', 'plugin_tags.plugin_id')
         ->join('tag', 'plugin_tags.tag_id', '=', 'tag.id')
         ->where('tag.id', '=', $tag->id);
      return $query;
   }

   public function scopeWithGlpiVersion($query, $version) {
      $query->join('plugin_version', 'plugin.id', '=', 'plugin_version.plugin_id')
            ->where('plugin_version.compatibility', '=', $version);
      return $query;
   }
}