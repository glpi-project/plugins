<?php

namespace API\Model;

use \Illuminate\Database\Capsule\Manager as DB;
use \Illuminate\Database\Eloquent\Model;

class PluginVersion extends Model {
    protected $table = 'plugin_version';
    public $timestamps = false;
    public $visible = ['num', 'compatibility'];

    public function plugin() {
        $this->belongsTo('\API\Model\Plugin');
    }
}