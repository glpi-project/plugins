<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class PluginAuthor extends Model {
	protected $table = 'plugin_author';
	//protected $visible = ['author'];

	public function plugin() {
		return $this->belongsTo('\API\Model\Plugin');
	}

	public function scopeWithPluginCount($query, $limit=10) {
		$query->select(['plugin_author.author', DB::raw('COUNT(author) as plugin_count')])
		      ->groupBy('author')
		      ->orderBy('plugin_count', 'DESC')
		      ->take($limit);
		return $query;
	}

}