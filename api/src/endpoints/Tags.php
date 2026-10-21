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
use \API\Model\Plugin;
use \API\Model\Tag;
use \API\OAuthServer\OAuthHelper;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @var \Slim\App $app
 */
global $app;

$tags_all = Tool::makeEndpoint(function(Request $request, Response $response) {
   OAuthHelper::needsScopes(['tags']);

   $tags = Tag::withUsage()
              ->orderBy('plugin_count', 'DESC');
   $tags_lang = clone $tags;
   $tags_lang = $tags_lang->withLang(Tool::getRequestLang($request));
   if (Tool::preCountQuery($tags_lang) == 0) {
      $tags = $tags->withLang('en');
   } else {
      $tags = $tags->withLang(Tool::getRequestLang($request));
   }

   return Tool::paginateCollection($tags, $request)->toResponse();
});

$tags_top = Tool::makeEndpoint(function(Request $request, Response $response) {
   OAuthHelper::needsScopes(['tags']);

   $tags = Tag::withUsage()
            ->orderBy('plugin_count', 'DESC');

   $tags_lang = clone $tags;
   $tags_lang = $tags_lang->withLang(Tool::getRequestLang($request));
   if (Tool::preCountQuery($tags_lang) == 0) {
      $tags = $tags->withLang('en');
   } else {
      $tags = $tags->withLang(Tool::getRequestLang($request));
   }

   return Tool::paginateCollection($tags, $request)->toResponse();
});

$tag_single = Tool::makeEndpoint(function(Request $request, Response $response, $key) {
   OAuthHelper::needsScopes(['tag']);

   $tag = Tag::where('key', '=', $key)->first();
   if ($tag == NULL) {
      throw new \API\Exception\ResourceNotFound('Tag', $key);
   }

   return $response->withJson($tag);
});

$tag_plugins = Tool::makeEndpoint(function(Request $request, Response $response, $key) {
   OAuthHelper::needsScopes(['tag', 'plugins']);

   $tags = Tag::where('key', '=', $key)->get();
   if ($tags->isEmpty()) {
      throw new \API\Exception\ResourceNotFound('Tag', $key);
   }

   $plugins = Tool::paginateCollection(
       Plugin::with('versions', 'authors')
             ->short()
             ->withAverageNote()
             ->descWithLang(Tool::getRequestLang($request))
             ->withTags($tags),
       $request
   );

   return $plugins->toResponse();
});

// HTTP rest map
$app->get('/tags', $tags_all);
$app->get('/tags/top', $tags_top);
$app->get('/tags/{key}/plugin', $tag_plugins);
$app->get('/tags/{key}', $tag_single);

$app->options('/tags', function(){});
$app->options('/tags/top', function(){});
$app->options('/tags/{key}/plugin', function(){});
$app->options('/tags/{key}', function(){});
