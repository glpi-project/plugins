<?php

namespace API\Model;

use Illuminate\Database\Eloquent\Model;

class PluginDescription extends Model {
   protected $table = 'plugin_description';
   protected $visible = ['description', 'lang'];
 
   public function plugin() {
      return $this->belongsTo('\API\Model\Plugin');
   }
}