<?php

require 'vendor/autoload.php';

$db = (new \API\DB)->get();
$app = new \Slim\Slim();

$app->get('/plugin', function() use ($app, $db) {
	$app->response->headers->set('Content-Type', 'application/json');
	echo json_encode($db->table('plugin')->get());
});

$app->run();