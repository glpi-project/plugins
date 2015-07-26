<?php

require 'vendor/autoload.php';

\API\Core\DB::initCapsule();
$app = new \Slim\Slim();

// Loading all REST modules
// with their endpoints like that:
// inside 'src/endoints'
$dir_endpoints = opendir('src/endpoints');
while ($ent = readdir($dir_endpoints)) {
	// For each .php file
	if (preg_match('/^(.*)\.php$/', $ent, $m)) {
		$endpoint = $m[0];
		// Read the file with PHP
		require 'src/endpoints/' . $endpoint;
	}
}
closedir($dir_endpoints);

// Ready to serve with Slim
$app->run();