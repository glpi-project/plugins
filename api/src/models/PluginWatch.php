<?php

namespace API\Model;

use \Illuminate\Database\Capsule\Manager as DB;
use \Illuminate\Database\Eloquent\Model;

class PluginWatch extends Model {
   protected $table = 'user_plugin_watch';
   public $timestamps = false;

   public function user() {
      return $this->belongsTo('\API\Model\User');
   }

   public function plugin() {
      return $this->belongsTo('\API\Model\Plugin');
   }
}