<?php

require 'vendor/autoload.php';

\API\Core\DB::initCapsule();
use \API\Core\Tool;
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

// Logs to error_log specified in virtualhost
$app->error(function(\Exception $e) {
   Tool::endWithJson([
      "error" => "Server error"
   ], 500);
   Tool::log($e->getMessage());
});

// JSON 404 response
$app->notFound(function() {
    Tool::endWithJson([
        "error" => "invalid endpoint"
    ], 404);
});

// Ready to serve with Slim
$app->run();