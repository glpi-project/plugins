<?php

require 'vendor/autoload.php';
require 'config.php'; // need database credentials
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection($db_settings);
$capsule->bootEloquent();

$app = new \Slim\Slim();

$app->get('/', function() use ($app) {
	$app->response->headers->set('Content-Type', 'application/json');
	// here, generating an empty JSON
	// object, this is a test route and this is
	// going to be removed in upcoming
	// commits
	echo json_encode(new stdClass);
});

$app->run();