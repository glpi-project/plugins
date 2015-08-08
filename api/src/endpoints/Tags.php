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

$tags_all = function() use ($app) {
    $tags = Tag::withUsage()
               ->orderBy('plugin_count', 'DESC')
               ->get();
    Tool::endWithJson($tags);
};

$tags_top = function() use ($app) {
    $tags = Tag::withUsage()
               ->orderBy('plugin_count', 'DESC')
               ->limit(10)
               ->get();
    Tool::endWithJson($tags);
};

$tag_single = function($key) use($app) {
  $tag = Tag::where('key', '=', $key)->first();
  if ($tag == NULL)
    return Tool::endWithJson([
            "error" => "Tag not found"
        ], 400);
  Tool::endWithJson($tag);
};

$tag_plugins = function($key) use($app) {
    $tag = Tag::where('key', '=', $key)->first();
    if ($tag == NULL) {
        return Tool::endWithJson([
            "error" => "Tag not found"
        ], 400);
    }

    $plugins = Plugin::with('versions', 'authors')
                     ->short()
                     ->withDownloads()
                     ->withAverageNote()
                     ->descWithLang(Tool::getRequestLang())
                     ->withTag($tag)
                     ->get();
    Tool::endWithJson($plugins);
};

// HTTP rest map
$app->get('/tags', $tags_all);
$app->get('/tags/top', $tags_top);
$app->get('/tags/:id/plugin', $tag_plugins);
$app->get('/tags/:id', $tag_single);


$app->options('/tags', function(){});
$app->options('/tags/top', function(){});
$app->options('/tags/:id/plugin', function($id){});
$app->options('/tags/:id', function($id){});