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

$all = function() use ($app) {
    $tags = Tag::withUsage()
               ->orderBy('plugin_count', 'DESC')
               ->get();
    Tool::endWithJson($tags);
};

$top = function() use ($app) {
    $tags = Tag::withUsage()
               ->orderBy('plugin_count', 'DESC')
               ->limit(10)
               ->get();
    Tool::endWithJson($tags);
};

$tag_plugins = function($id) use($app) {
    $tag = Tag::find($id);
    if ($tag == NULL) {
        Tool::endWithJson([
            "error" => "Tag not found"
        ], 400);
    }

    $plugins = Plugin::with('versions', 'authors')
                     ->short()
                     ->withDownloads()
                     ->withAverageNote()
                     ->descWithLang($tag->lang)
                     ->withTag($tag)
                     ->get();
    Tool::endWithJson($plugins);
};

// HTTP rest map
$app->get('/tags', $all);
$app->get('/tags/top', $top);
$app->get('/tags/:id/plugin', $tag_plugins);