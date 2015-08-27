<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Author extends Model {
   protected $table = 'author';
   protected $visible = ['id', 'name', 'plugin_count'];
   public $timestamps = false;

   public function plugins() {
      return $this->belongsToMany('\API\Model\Plugin', 'plugin_author');
   }

   public function scopeWithPluginCount($query) {
      $query->select(['author.id', 'author.name', DB::raw('COUNT(plugin_author.plugin_id) as plugin_count')])
            ->leftJoin('plugin_author', 'author.id', '=', 'plugin_author.author_id')
            ->groupBy('plugin_author.author_id');
      return $query;
   }

   public function scopeContributorsOnly($query) {
      $newQuery = \Illuminate\Database\Capsule\Manager::table(
                        \Illuminate\Database\Capsule\Manager::raw(
                           "({$query->toSql()}) as sub"))
                              ->mergeBindings($query->getQuery())
                              ->where('plugin_count', '!=', '0');
      return $newQuery;
   }

   public function scopeMostActive($query, $limit = false) {
      $query->select(['author.id', 'author.name', DB::raw('COUNT(plugin_author.plugin_id) as plugin_count')])
            ->leftJoin('plugin_author', 'author.id', '=', 'plugin_author.author_id')
            ->groupBy('author.name')
            ->orderBy('plugin_count', 'DESC');
      if ($limit != false) {
         $query->take($limit);
      }
      return $query;
   }

}