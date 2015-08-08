<?php
/**
 * Author
 *
 * This REST module hooks on
 * following URLs
 *
 * /author
 */


use \API\Core\Tool;
use \Illuminate\Database\Capsule\Manager as DB;

$all = function() use($app) {
   $all = \API\Model\Author::mostActive(10)->get();
   Tool::endWithJson($all);
};

$single = function($id) use($app) {
    $single = \API\Model\Author::withPluginCount()
                               ->find($id);
    Tool::endWithJson($single);
};

$author_plugins = function($id) use($app) {
    $author_plugins = \API\Model\Author::find($id);
    if (!$author_plugins)
        return Tool::endWithJson([
            "error" => "Cannot find author"
        ]);
    Tool::endWithJson($author_plugins->plugins()
                                     ->with('versions', 'authors')
                                     ->short()
                                     ->withDownloads()
                                     ->withAverageNote()
                                     ->descWithLang('en')
                                     ->get());
};

// HTTP REST Map
$app->get('/author', $all);
$app->get('/author/:id', $single);
$app->get('/author/:id/plugin', $author_plugins);

$app->options('/author',function(){});
$app->options('author/:id',function($id){});
$app->options('/author/:id/plugin',function($id){});