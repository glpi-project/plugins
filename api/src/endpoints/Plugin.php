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
use \API\Core\ValidableXMLPluginDescription;

/**
 * Fetching infos of a single plugin
 */
$single = function($key) use($app) {
   $plugin = Plugin::with('descriptions', 'authors', 'versions', 'screenshots', 'tags')
                  ->short()
                  ->withAverageNote()
                  ->withNumberOfVotes()
                  ->withCurrentVersion()
                  ->withDownloads()
                  ->where('key', '=', $key)
                  ->where('active', '=', 1)
                  ->first();

   if ($plugin) {
      Tool::endWithJson($plugin);
   } else {
      Tool::endWithJson([ 
       'error' => 'No plugin has that key'
      ], 400);
   }
};

/**
 * List of all plugins
 */
$all = function() use($app) {
   $plugins = Tool::paginateCollection(
                 Plugin::short()
                       ->with('authors', 'versions', 'descriptions')
                       ->withDownloads()
                       ->withAverageNote()
                       ->descWithLang(Tool::getRequestLang())
                       ->where('active', '=', 1));
   Tool::endWithJson($plugins);
};

/**
 * Most popular plugins
 */
$popular = function() use($app) {
   $popular_plugins = Plugin::popularTop(10)
                            ->where('active', '=', 1)
                            ->get();
   Tool::endWithJson($popular_plugins);
};

/**
 * Trending plugins
 *  most popular the 2 last weeks
 */
$trending = function() use($app) {
   $trending_plugins = Plugin::trendingTop(10)
                             ->where('active', '=', 1)
                             ->get();
   Tool::endWithJson($trending_plugins);
};

/**
 * Updated plugins
 *  most recently updated plugins
 */
$updated = function() use($app) {
   $updated_plugins = Plugin::updatedRecently(10)
                            ->where('active', '=', 1)
                            ->get();
   Tool::endWithJson($updated_plugins);
};

/**
 * New plugins
 *  most recently added plugins
 */
$new = function() use($app) {
  $new_plugins = Plugin::mostFreshlyAddedPlugins(10)
                       ->where('active', '=', 1)
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

/**
 * Method called when an user submits a plugin
 */
$submit = function() use($app) {
    $body = Tool::getBody();
    $fields = ['plugin_url'];

    $recaptcha = new ReCaptcha(Tool::getConfig()['recaptcha_secret']);
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

    // Quickly validating
    if (Plugin::where('xml_url', '=', $body->plugin_url)->count() > 0)
      return  Tool::endWithJson([
          "error" => "That plugin XML URL has already been submitted."
      ]);

    $xml = @file_get_contents($body->plugin_url);
    if (!$xml)
      return  Tool::endWithJson([
          "error" => "We cannot fetch that URL."
      ]);

    $xml = new ValidableXMLPluginDescription($xml);
    if (!$xml->isValid())
      return Tool::endWithJson([
          "error" => "Unreadable/Non validable XML.",
          "details" => $xml->errors
      ]);
    $xml = $xml->contents;

    if (Plugin::where('key', '=', $xml->key)->count() > 0)
      return Tool::endWithJson([
          "error" => "Your XML describe a plugin whose key already exists in our database."
      ]);

    $plugin = new Plugin;
    $plugin->xml_url = $body->plugin_url;
    $plugin->date_added = DB::raw('NOW()');
    $plugin->active = false;
    $plugin->save();


   $msg_alerts_settings = Tool::getConfig()['msg_alerts'];
   $recipients = ''; $i = 0;
   foreach ($msg_alerts_settings['recipients'] as $recipient) {
      if ($i > 0)
         $recipients .= ', ';
      $recipients .= $recipient;
      $i++;
   }

   mail($recipients,
      $msg_alerts_settings['subject_prefix'].'[PLUGIN SUBMISSION] '.$xml->name.' ('.$xml->key.')',
      'A new plugin "'.$xml->name.'" with key "'.$xml->key.'" has been submitted and is awaiting to be verified. It has db id #'.$plugin->id,
      "From: GLPI Plugins <plugins@glpi-project.org>");

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

$app->options('/plugin',function(){});
$app->options('/plugin/popular',function(){});
$app->options('/plugin/trending',function(){});
$app->options('/plugin/updated',function(){});
$app->options('/plugin/new',function(){});
$app->options('/plugin/star',function(){});
$app->options('/plugin/:id',function($id){});