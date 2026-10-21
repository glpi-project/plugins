<?php

use \API\Core\Tool;
use API\OAuthServer\OAuthHelper;
use Slim\Http\Request;
use Slim\Http\Response;

error_reporting(E_ALL & ~E_DEPRECATED); // Suppress deprecation messages

require 'vendor/autoload.php';

// Initialisation of Database (Illuminate)
// and webapp global object
\API\Core\DB::initCapsule();
$app = new \Slim\App();

// Instantiating the Resource Server
global $resourceServer;
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
$app->getContainer()['notFoundHandler'] = function ($c) {
    return function (Request $request, Response $response) use ($c) {
        return $response->withJson([], 404);
    };
};

// Welcoming browsers when they reach /api
$app->get('/', function (Request $request, Response $response, $args = []) {
   return $response->withStatus(200);
});

// Ready to serve with Slim
$app->run();
