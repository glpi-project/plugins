<?php

require 'vendor/autoload.php';

\API\DB::initCapsule();
$app = new \Slim\Slim();

$app->get('/plugin', function() use ($app) {
	$app->response->headers->set('Content-Type', 'application/json');
	echo json_encode(\API\Plugin::with('descriptions', 'authors')->get()->toArray());
});

$app->run();