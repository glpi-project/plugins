<?php

namespace API\Model;

use \Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model {
   protected $table = 'tag';
   public $timestamps = false;
   public $visible = ['key', 'lang', 'tag', 'plugin_count'];

   public function scopeWithUsage($query) {
     $query->addSelect(['*', DB::raw('(SELECT COUNT(*) FROM plugin_tags WHERE tag_id = tag.id) as plugin_count')]);
     return $query;
   }

   public function scopeWithLang($query, $lang) {
      $query->where('tag.lang', '=', $lang);
      return $query;
   }

   public function plugins() {
   	return $this->belongsToMany('\API\Model\Plugin', 'plugin_tags');
   }
}