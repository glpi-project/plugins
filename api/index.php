<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->get('/', function() {
	// here, generating an empty JSON
	// bject, this is a test route and this is
	// going to be removed in upcoming
	// commits
	echo json_encode(new stdClass);
});

$app->run();