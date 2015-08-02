<?php

namespace API\Model;

use \Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model {
    protected $table = 'tag';

    public function scopeWithUsage($query) {
      $query->addSelect(['*', DB::raw('(SELECT COUNT(*) FROM plugin_tags WHERE tag_id = tag.id) as plugin_count')]);
      return $query;
    }
}