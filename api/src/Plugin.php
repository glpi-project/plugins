<?php

namespace API;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model {
	protected $table = 'plugin';

	public function descriptions() {
		return $this->hasMany('PluginDescription');
	}

	public function authors() {
		return $this->hasMany('PluginAuthor');
	}
}