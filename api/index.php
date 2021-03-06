<?php

require 'vendor/autoload.php';
use \API\Core\Tool;
use API\OAuthServer\OAuthHelper;

// Initialisation of Database (Illuminate)
// and webapp global object
\API\Core\DB::initCapsule();
$app = new \Slim\Slim();

// Instantiating the Resource Server
$resourceServer = new \League\OAuth2\Server\ResourceServer(
  OAuthHelper::getSessionStorage(),
  OAuthHelper::getAccessTokenStorage(),
  OAuthHelper::getClientStorage(),
  OAuthHelper::getScopeStorage()
);

// Loading all REST modules
// with their endpoints like that:
// inside 'src/endpoints'
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

// JSON 404 response
$app->notFound(Tool::makeEndpoint(function() {
   throw new \API\Exception\InvalidEndpoint;
}));

// Welcoming browsers when they reach /api
$app->get('/', function() use($app) {
   $app->halt(200);
   //echo file_get_contents(__DIR__.'/welcome.html');
});

// Ready to serve with Slim
$app->run();
