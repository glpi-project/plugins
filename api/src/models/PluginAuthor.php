<?php

namespace API\Model;

use Illuminate\Database\Eloquent\Model;

class PluginAuthor extends Model {
	protected $table = 'plugin_author';
	protected $visible = ['author'];

	public function plugin() {
		return $this->belongsTo('\API\Model\Plugin');
	}
}