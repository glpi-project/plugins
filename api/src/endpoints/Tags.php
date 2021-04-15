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
use \Illuminate\Database\Capsule\Manager as DB;
use \API\OAuthServer\OAuthHelper;

$tags_all = Tool::makeEndpoint(function() use ($app) {
   OAuthHelper::needsScopes(['tags']);

   $tags = Tool::paginateCollection(Tag::withUsage()
               ->orderBy('plugin_count', 'DESC'));
   $tags = Tag::withUsage()
              ->orderBy('plugin_count', 'DESC');
   $tags_lang = clone $tags;
   $tags_lang = $tags_lang->withLang(Tool::getRequestLang());
   if (Tool::preCountQuery($tags_lang) == 0) {
      $tags = $tags->withLang('en');
   } else {
      $tags = $tags->withLang(Tool::getRequestLang());
   }
   Tool::endWithJson(Tool::paginateCollection($tags));
});

$tags_top = Tool::makeEndpoint(function() use ($app) {
   OAuthHelper::needsScopes(['tags']);

   $tags = Tag::withUsage()
            ->orderBy('plugin_count', 'DESC');

   $tags_lang = clone $tags;
   $tags_lang = $tags_lang->withLang(Tool::getRequestLang());
   if (Tool::preCountQuery($tags_lang) == 0) {
      $tags = $tags->withLang('en');
   } else {
      $tags = $tags->withLang(Tool::getRequestLang());
   }
   Tool::endWithJson(Tool::paginateCollection($tags));
});

$tag_single = Tool::makeEndpoint(function($key) use($app) {
   OAuthHelper::needsScopes(['tag']);

   $tag = Tag::where('key', '=', $key)->first();
   if ($tag == NULL) {
      throw new \API\Exception\ResourceNotFound('Tag', $key);
   }
  Tool::endWithJson($tag);
});

$tag_plugins = Tool::makeEndpoint(function($key) use($app) {
   OAuthHelper::needsScopes(['tag', 'plugins']);

   $tags = Tag::where('key', '=', $key)->get();
   if ($tags->isEmpty()) {
      throw new \API\Exception\ResourceNotFound('Tag', $key);
   }

   $plugins = Tool::paginateCollection(Plugin::with('versions', 'authors')
                ->short()
                ->withAverageNote()
                ->descWithLang(Tool::getRequestLang())
                ->withTags($tags));
   Tool::endWithJson($plugins);
});

// HTTP rest map
$app->get('/tags', $tags_all);
$app->get('/tags/top', $tags_top);
$app->get('/tags/:id/plugin', $tag_plugins);
$app->get('/tags/:id', $tag_single);

$app->options('/tags', function(){});
$app->options('/tags/top', function(){});
$app->options('/tags/:id/plugin', function($id){});
$app->options('/tags/:id', function($id){});
