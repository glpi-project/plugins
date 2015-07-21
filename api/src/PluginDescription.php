<?php

namespace API;

use Illuminate\Database\Eloquent\Model;

class PluginDescription extends Model {
	protected $table = 'plugin_description';
 
	public function plugin() {
		return $this->belongsTo('Plugin');
	}
}