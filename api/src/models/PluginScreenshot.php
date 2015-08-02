<?php

namespace API\Model;

use \Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class PluginScreenshot extends Model {
    protected $table = 'plugin_screenshot';
    public $timestamps = false;
    public $visible = ['url'];

    public function plugin() {
        $this->belongsTo('\API\Model\Plugin');
    }

}