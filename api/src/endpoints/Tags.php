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

// HTTP rest map
$app->get('/tags', $all);
$app->get('/tags/top', $top);