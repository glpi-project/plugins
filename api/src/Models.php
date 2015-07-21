<?php

namespace API;

use Illuminate\Database\Eloquent\Model;

class PluginDescription extends Model {
	protected $table = 'plugin_description';
 
	public function plugin() {
		return $this->belongsTo('Plugin');
	}
}

class PluginAuthor extends Model {
	protected $table = 'plugin_author';

	public function plugin() {
		return $this->belongsTo('Plugin');
	}
}

class Plugin extends Model {
	protected $table = 'plugin';

	public function descriptions() {
		return $this->hasMany('PluginDescription');
	}

	public function authors() {
		return $this->hasMany('PluginAuthor');
	}
}