<?php

namespace API\Model;

use \Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model {
    protected $table = 'plugin';

    protected $casts = [
      "note" => 'float'
    ];

    public function descriptions() {
        return $this->hasMany('\API\Model\PluginDescription');
    }

    public function authors() {
        return $this->hasMany('\API\Model\PluginAuthor');
    }

    public function stars() {
        return $this->hasMany('\API\Model\PluginStar');
    }

    public function downloads() {
        return $this->hasMany('\API\Model\PluginDownload');
    }

    public function versions() {
        return $this->hasMany('\API\Model\PluginVersion');
    }

    public function scopeShort($query) {
        $query->select(['plugin.id', 'plugin.name']);
        return $query;
    }

    public function scopeDescWithLang($query, $lang) {
        $query->addSelect(['plugin_description.short_description'])
              ->leftJoin('plugin_description', 'plugin.id', '=', 'plugin_description.plugin_id')
              ->groupBy('plugin.name')
              ->where('plugin_description.lang', '=', $lang);
        return $query;
    }

    public function scopeWithAverageNote($query) {
        $query->select(['plugin.*', DB::raw('AVG(plugin_stars.note) as note')])
              ->leftJoin('plugin_stars', 'plugin.id', '=', 'plugin_stars.plugin_id')
              ->groupBy('plugin.name');
    }

    public function scopeWithDownloads($query, $limit = false) {
        $query->addSelect([DB::raw('(SELECT COUNT(*) FROM plugin_download where plugin_download.plugin_id = plugin.id) as downloaded')])
                     ->leftJoin('plugin_download', 'plugin.id', '=', 'plugin_download.plugin_id')
                     ->groupBy('plugin.name');
        return $query;
    }

    public function scopePopularTop($query, $limit = 10) {
        $query->select(['plugin.id', 'plugin.name',
                                DB::raw('COUNT(name) as downloaded')])
                     ->join('plugin_download', 'plugin.id', '=', 'plugin_download.plugin_id')
                     ->groupBy('name')
                     ->orderBy('downloaded', 'DESC')
                     ->take(10);
        return $query;
    }

    public function scopeTrendingTop($query, $limit = 10) {
        $query->select(['plugin.id', 'plugin.name',
                                DB::raw('COUNT(name) as downloaded')])
                     ->join('plugin_download', 'plugin.id', '=', 'plugin_download.plugin_id')
                     ->where('downloaded_at', '>', DB::raw('NOW() - INTERVAL 1 WEEK'))
                     ->groupBy('name')
                     ->orderBy('downloaded', 'DESC')
                    ->take(10);
        return $query;
    }

    public function scopeUpdatedRecently($query, $limit = 10) {
        $query->select(['plugin.id', 'plugin.name', 'date_updated'])
              ->orderBy('date_updated', 'DESC')
              ->take($limit);
        return $query;
    }

    public function scopeWithCurrentVersion($query) {
        $query->addSelect([DB::raw('plugin_version.compatibility as compatible_with')])
              ->join('plugin_version', 'plugin.id', '=', 'plugin_version.plugin_id');
        return $query;
    }
}