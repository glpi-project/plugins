<?php

namespace API\Model;

use Illuminate\Database\Eloquent\Model;

class PluginDownload extends Model {
	protected $table = 'plugin_download';
   public $timestamps = false;

	public function plugin() {
		$this->belongsTo('\API\Model\Plugin');
	}
}
