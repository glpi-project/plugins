<?php

namespace API\Model;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model {
	protected $table = 'plugin';

	public function descriptions() {
		return $this->hasMany('\API\Model\PluginDescription');
	}

	public function authors() {
		return $this->hasMany('\API\Model\PluginAuthor');
	}
}