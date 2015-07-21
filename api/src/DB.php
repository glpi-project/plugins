<?php

namespace API;

use Illuminate\Database\Capsule\Manager as Capsule;

class DB {
	private $capsule = NULL;

	public function __construct() {
		require 'config.php'; // need database credentials
		$capsule = new Capsule;
		$capsule->addConnection($db_settings);
		$capsule->bootEloquent();
		$capsule->setAsGlobal();
		$this->capsule = $capsule;
	}

	public function get() {
		return $this->capsule;
	}
}