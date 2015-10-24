<?php

namespace API\Model;

use \Illuminate\Database\Capsule\Manager as DB;
use \Illuminate\Database\Eloquent\Model;

class PluginLang extends Model {
   protected $table = 'plugin_lang';
   public $timestamps = false;
   public $visible = ['lang'];

   public function plugins() {
      return $this->belongsToMany('\API\Model\Plugin', 'plugin_plugin_lang');
   }
}