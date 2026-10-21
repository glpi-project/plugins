<?php
/**
 * Search
 *
 * This REST module hooks on
 * following URLs
 *
 * /search
 */

use \API\Core\Tool;
use \API\OAuthServer\OAuthHelper;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @var \Slim\App $app
 */
global $app;

// Minimal length of search string
$search_min_length = 2;

$search = Tool::makeEndpoint(function(Request $request, Response $response) {
   OAuthHelper::needsScopes(['plugins:search']);

   global $search_min_length;

   $body = $request->getParsedBody();
   if ($body == NULL ||
      !isset($body['query_string']) ||
      strlen($body['query_string']) < $search_min_length ) {

     return $response->withJson([
        "error" => "Your search string needs to ".
                 "have at least ".$search_min_length." chars"
     ], 400);
   }
   $query_string = $body['query_string'];

   $_search = Tool::paginateCollection(
      \API\Model\Plugin::short()
                       ->with('authors', 'versions', 'descriptions')
                      ->withAverageNote()
                       ->descWithLang(Tool::getRequestLang($request))
                       ->where('active', '=', true)
                       ->where(function($q) use($query_string) {
                         return $q->where('name', 'LIKE', "%$query_string%")
                                  ->orWhere('key', 'LIKE', "%$query_string%")
                                  ->orWhere('plugin_description.short_description', 'LIKE', "%$query_string%")
                                  ->orWhere('plugin_description.long_description', 'LIKE', "%$query_string%");
                       })
                       ->orderBy('download_count', 'DESC')
                       ->orderBy('note', 'DESC')
                       ->orderBy('name', 'ASC'),
      $request
   );

   return $_search->toResponse();
});

$app->post('/search', $search);
$app->options('/search', function(){});
