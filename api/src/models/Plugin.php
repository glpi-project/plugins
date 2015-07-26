<?php

namespace API\Model;

use \Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model {
    protected $table = 'plugin';

    public function descriptions() {
        return $this->hasMany('\API\Model\PluginDescription');
    }

    public function authors() {
        return $this->hasMany('\API\Model\PluginAuthor');
    }

    public function scopeWithDownloads($query, $limit = false) {
        $q = $query->select(['plugin.*',
                             DB::raw('IF(COUNT(name)>1,COUNT(name),0) as downloaded')])
                     ->leftJoin('plugin_download', 'plugin.id', '=', 'plugin_download.plugin_id')
                     ->groupBy('name');
        return $q;
    }

    public function scopePopularTop($query, $limit = 10) {
        $q = $query->select(['plugin.id', 'plugin.name',
                                DB::raw('COUNT(name) as downloaded')])
                     ->join('plugin_download', 'plugin.id', '=', 'plugin_download.plugin_id')
                     ->groupBy('name')
                     ->orderBy('downloaded', 'DESC');
                     if ($limit)  {
                        $query->take(10);
                     }
        return $q;
    }

    public function scopeTrendingTop($query, $limit = 10) {
        $q = $query->select(['plugin.id', 'plugin.name',
                                DB::raw('COUNT(name) as downloaded')])
                     ->join('plugin_download', 'plugin.id', '=', 'plugin_download.plugin_id')
                     ->where('downloaded_at', '>', DB::raw('NOW() - INTERVAL 1 WEEK'))
                     ->groupBy('name')
                     ->orderBy('downloaded', 'DESC');
                     if ($limit)  {
                        $query->take(10);
                     }
        return $q;
    }

    public function scopeUpdatedRecently($query, $limit = 10) {
        $q = $query->where('date_updated', '>', DB::raw('NOW() - INTERVAL 2 WEEK'));
    }
}