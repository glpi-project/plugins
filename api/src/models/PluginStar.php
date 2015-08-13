<?php

namespace API\Model;

use \Illuminate\Database\Capsule\Manager as DB;
use \Illuminate\Database\Eloquent\Model;

class PluginStar extends Model {
   protected $table = 'plugin_stars';
   public $timestamps = false;

   public function plugin() {
      $this->belongsTo('\API\Model\Plugin');
   }
}