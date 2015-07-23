<?php

require 'vendor/autoload.php';

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
	// This is the typical mysql query:
	//    SELECT name, count(name) FROM plugin
	//    INNER JOIN plugin_download
	//    ON plugin.id = plugin_download.plugin_id
	//    GROUP BY name;
	echo json_encode(new stdClass);
//	echo json_encode(\API\Model\Plugin::with('description', 'authors'));
});

// Star a plugin
$app->get('/plugin/star', function() use($app) {

});

$app->run();