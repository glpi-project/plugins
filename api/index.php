<?php

require 'vendor/autoload.php';

$db = (new \API\DB)->get();
$app = new \Slim\Slim();

$app->get('/plugin', function() use ($app) {
	$app->response->headers->set('Content-Type', 'application/json');
	echo json_encode(\API\Plugin::get()->toArray());
});

$app->get('/test', function() use($app) {
	$app->response->headers->set('Content-Type', 'application/json');
	echo json_encode(\API\Plugin::first()->authors);
});

$app->run();