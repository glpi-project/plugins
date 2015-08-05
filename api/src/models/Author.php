<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Author extends Model {
   protected $table = 'author';
   protected $visible = ['name', 'plugin_count'];
   public $timestamps = false;

   public function plugins() {
      return $this->belongsToMany('\API\Model\Plugin', 'plugin_author');;
   }
   // public function plugin() {
   //    return $this->belongsTo('\API\Model\Plugin');
   // }

   // public function scopeMostActive($query, $limit=10) {
   //    $query->select(['*', 'plugin_author.author', DB::raw('COUNT(plugin_author.plugin_id) as plugin_count')])
   //          ->groupBy('plugin_author.author')
   //          ->orderBy('plugin_count', 'DESC')
   //          ->take($limit);
   //    return $query;
   // }

}