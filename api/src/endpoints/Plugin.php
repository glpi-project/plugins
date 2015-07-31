<?php
/**
 * Plugin
 *
 * This REST module hooks on
 * following URLs
 *
 * /plugin
 * /plugin/popular
 * /plugin/trending
 * /plugin/star
 */


use \API\Core\Tool;
use \Illuminate\Database\Capsule\Manager as DB;
use \API\Model\Plugin;
use \API\Model\PluginStar;

/**
 * Fetching infos of a single plugin
 */
$single = function($id) use($app) {
   $plugin = Plugin::with('descriptions', 'authors')
                   ->withAverageNote()
                   ->find($id);
   if ($plugin) {
      Tool::endWithJson($plugin);
   } else {
      Tool::endWithJson([
         'error' => 'No plugin has that index'
      ]);
   }
};

/**
 * List of all plugins
 */
$all = function() use($app) {
   $all = Plugin::withDownloads()
                           ->with('descriptions', 'authors')
                           ->get()->toArray();
   Tool::endWithJson($all);
};

/**
 * Most popular plugins
 */
$popular = function() use($app) {
   $popular_plugins = Plugin::popularTop(10)
                            ->get();
   Tool::endWithJson($popular_plugins);
};

/**
 * Trending plugins
 *  most popular the 2 last weeks
 */
$trending = function() use($app) {
   $trending_plugins = Plugin::trendingTop(10)
                                        ->get();
   Tool::endWithJson($trending_plugins);
};

$updated = function() use($app) {
   $updated_plugins = Plugin::updatedRecently(10)
                                       ->get();
   Tool::endWithJson($updated_plugins);
};

/**
 * Remote procedure to star a plugin
 */
$star = function() use($app) {
   $body = Tool::getBody();

   if (!isset($body->plugin_id) ||
       !isset($body->note) ||
       !is_numeric($body->plugin_id) ||
       !is_numeric($body->note))
      return Tool::endWithJson([
         "error" => "plugin_id and note should be provided as integer"
      ], 400);

   $plugin = Plugin::find($body->plugin_id);

   if ($plugin == NULL)
      return Tool::endWithJson([
         "error" => "you try to note a plugin that doesn't exists"
      ], 400);


   $plugin_star = new PluginStar();
   $plugin_star->note = $body->note;
   $plugin_star->date = DB::raw('NOW()');

   $plugin->stars()->save($plugin_star);

   $plugin = Plugin::withAverageNote()
                   ->find($body->plugin_id);
   // returning new average
   Tool::endWithJson([
      "new_average" => $plugin->note
   ]);
};

// HTTP REST Map
$app->get('/plugin', $all);
$app->get('/plugin/popular', $popular);
$app->get('/plugin/trending', $trending);
$app->get('/plugin/updated', $updated);
$app->post('/plugin/star', $star);
$app->get('/plugin/:id', $single);