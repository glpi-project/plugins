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
   $all = \API\Model\Author::mostActive()->get();
   Tool::endWithJson($all);
};

$top = function() use($app) {
   $top = \API\Model\Author::mostActive(10)->get();
   Tool::endWithJson($top);
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
                                     ->descWithLang(Tool::getRequestLang())
                                     ->get());
};

// HTTP REST Map
$app->get('/author', $all);
$app->get('/author/top', $top);
$app->get('/author/:id', $single);
$app->get('/author/:id/plugin', $author_plugins);

$app->options('/author',function(){});
$app->options('/author/top',function(){});
$app->options('/author/:id',function($id){});
$app->options('/author/:id/plugin',function($id){});