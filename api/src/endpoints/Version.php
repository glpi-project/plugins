<?php

use \API\Core\Tool;
use \API\Model\Plugin;

use \API\OAuthServer\OAuthHelper;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @var \Slim\App $app
 */
global $app;

$version_plugins = Tool::makeEndpoint(function(Request $request, Response $response, $version) {
   OAuthHelper::needsScopes(['version', 'plugins']);

   $plugins = Tool::paginateCollection(
       Plugin::short()
             ->with('authors', 'versions', 'descriptions')
             ->withAverageNote()
             ->descWithLang(Tool::getRequestLang($request))
             ->withGlpiVersion($version),
       $request
   );

   return $plugins->toResponse();
});

$app->get('/version/{version}/plugin', $version_plugins);
$app->options('/version/{version}/plugin', function() {});
