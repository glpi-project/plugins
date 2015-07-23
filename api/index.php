<?php

require 'vendor/autoload.php';

use \Illuminate\Database\Capsule\Manager as DB;

\API\Core\DB::initCapsule();
$app = new \Slim\Slim();

// List of all plugins
$app->get('/plugin', function() use ($app) {
	$app->response->headers->set('Content-Type', 'application/json');
	echo json_encode(\API\Model\Plugin::with('descriptions', 'authors')->get()->toArray());
});

// List of all trending plugins
$app->get('/plugin/trending', function() use($app) {
	$app->response->headers->set('Content-Type', 'application/json');

	$trending_plugins = DB::table('plugin')
	      ->select(['plugin.name', DB::raw('COUNT(name) as downloaded')])
	      ->join('plugin_download', 'plugin.id', '=', 'plugin_download.plugin_id')
	      ->groupBy('name')
	      ->orderBy('downloaded', 'DESC')
	      ->get();

	echo json_encode($trending_plugins);
});

// Star a plugin
$app->get('/plugin/star', function() use($app) {

});

$app->run();