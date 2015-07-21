<?php

namespace API;

use Illuminate\Database\Eloquent\Model;

class PluginAuthor extends Model {
	protected $table = 'plugin_author';

	public function plugin() {
		return $this->belongsTo('Plugin');
	}
}