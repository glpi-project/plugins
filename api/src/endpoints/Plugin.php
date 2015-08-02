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
use \ReCaptcha\ReCaptcha;

require 'config.php';

/**
 * Fetching infos of a single plugin
 */
$single = function($id) use($app) {
   $plugin = Plugin::with('descriptions', 'authors', 'versions')
                   ->withAverageNote()
                   ->withCurrentVersion()
                   ->withDownloads()
                   ->find($id);
   if ($plugin) {
      Tool::endWithJson($plugin);
   } else {
      Tool::endWithJson([
         'error' => 'No plugin has that index'
      ], 400);
   }
};

/**
 * List of all plugins
 */
$all = function() use($app) {
   $all = Plugin::withDownloads()
                           ->with('descriptions', 'authors')
                           ->get();
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

$new = function() use($app) {
  $new_plugins = Plugin::mostFreshlyAddedPlugins(10)
                       ->get();
  Tool::endWithJson($new_plugins);
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

$submit = function() use($app, $recaptcha_secret) {
    $body = Tool::getBody();
    $fields = ['plugin_url'];

    $recaptcha = new ReCaptcha($recaptcha_secret);
    $resp = $recaptcha->verify($body->recaptcha_response);
    if (!$resp->isSuccess()) {
       return  Tool::endWithJson([
            "error" => "Recaptcha not validated"
        ]);
    }

    foreach($fields as $prop) {
        if (!property_exists($body, $prop))
            return  Tool::endWithJson([
                "error" => "Missing ". $prop
            ]);
    }

    $plugin = new Plugin;
    $plugin->xml_url = $body->plugin_url;
    $plugin->active = false;
    $plugin->save();

    return Tool::endWithJson([
        "success" => true
    ]);
};

// HTTP REST Map
$app->get('/plugin', $all);
$app->post('/plugin', $submit);
$app->get('/plugin/popular', $popular);
$app->get('/plugin/trending', $trending);
$app->get('/plugin/updated', $updated);
$app->get('/plugin/new', $new);
$app->post('/plugin/star', $star);
$app->get('/plugin/:id', $single);