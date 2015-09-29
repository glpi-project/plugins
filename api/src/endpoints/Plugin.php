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
use \API\Core\Mailer;
use \Illuminate\Database\Capsule\Manager as DB;
use \API\Model\User;
use \API\Model\Plugin;
use \API\Model\PluginStar;
use \ReCaptcha\ReCaptcha;
use \API\Core\ValidableXMLPluginDescription;
use \API\Exception\ResourceNotFound;
use \API\OAuthServer\OAuthHelper;
use \API\Exception\InvalidRecaptcha;
use \API\Exception\InvalidField;

/**
 * Fetching infos of a single plugin
 */
$single = Tool::makeEndpoint(function($key) use($app, $resourceServer) {
   OAuthHelper::needsScopes(['plugin:card']);


   $plugin = Plugin::with('descriptions', 'authors', 'versions', 'screenshots', 'tags')
                  ->short()
                  ->withAverageNote()
                  ->withNumberOfVotes()
                  ->where('key', '=', $key)
                  ->where('active', '=', 1)
                  ->first();

   if ($plugin) {
      if ($user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId()) {
         $plugin = $plugin->toArray();
         $user = User::where('id', '=', $user_id)->first();
         $plugin['watched'] = $user->watchs()->where('plugin_id', '=', $plugin['id'])->count() > 0;
      }
      Tool::endWithJson($plugin);
   } else {
      throw new \API\Exception\ResourceNotFound('Plugin', $key);
   }
});

/**
 * List of all plugins
 */
$all = Tool::makeEndpoint(function() use($app) {
   OAuthHelper::needsScopes(['plugins']);

   $plugins = Tool::paginateCollection(
                 Plugin::short()
                       ->with('authors', 'versions', 'descriptions')
                       ->withAverageNote()
                       ->descWithLang(Tool::getRequestLang())
                       ->where('active', '=', 1));
   Tool::endWithJson($plugins);
});

/**
 * Most popular plugins
 */
$popular = Tool::makeEndpoint(function() use($app) {
   OAuthHelper::needsScopes(['plugins']);

   $popular_plugins = Plugin::popularTop(10)
                            ->where('active', '=', 1)
                            ->get();
   Tool::endWithJson($popular_plugins);
});

/**
 * Trending plugins
 *  most popular the 2 last weeks
 */
$trending = Tool::makeEndpoint(function() use($app) {
   OAuthHelper::needsScopes(['plugins']);

   $trending_plugins = Plugin::trendingTop(10)
                             ->where('active', '=', 1)
                             ->get();
   Tool::endWithJson($trending_plugins);
});

/**
 * Updated plugins
 *  most recently updated plugins
 */
$updated = Tool::makeEndpoint(function() use($app) {
   OAuthHelper::needsScopes(['plugins']);

   $updated_plugins = Plugin::updatedRecently(10)
                            ->where('active', '=', 1)
                            ->get();
   Tool::endWithJson($updated_plugins);
});

/**
 * New plugins
 *  most recently added plugins
 */
$new = Tool::makeEndpoint(function() use($app) {
   OAuthHelper::needsScopes(['plugins']);

   $new_plugins = Plugin::mostFreshlyAddedPlugins(10)
                       ->where('active', '=', 1)
                       ->get();
   Tool::endWithJson($new_plugins);
});

/**
 * Remote procedure to star a plugin
 */
$star = Tool::makeEndpoint(function() use($app) {
   OAuthHelper::needsScopes(['plugin:star']);
   $body = Tool::getBody();

   if (!isset($body->plugin_id) ||
       !isset($body->note) ||
       !is_numeric($body->plugin_id) ||
       !is_numeric($body->note)) {
      Tool::endWithJson([
         "error" => "plugin_id and note should be provided as integer"
      ], 400);
   }

   $plugin = Plugin::find($body->plugin_id);

   if ($plugin == NULL) {
      Tool::endWithJson([
         "error" => "you try to note a plugin that doesn't exists"
      ], 400);
   }


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
});

/**
 * Method called when an user submits a plugin
 */
$submit = Tool::makeEndpoint(function() use($app) {
   OAuthHelper::needsScopes(['plugin:submit']);

   $body = Tool::getBody();

   $recaptcha = new ReCaptcha(Tool::getConfig()['recaptcha_secret']);
   $resp = $recaptcha->verify($body->recaptcha_response);
   if (!$resp->isSuccess()) {
      throw new InvalidRecaptcha;
   }

   if (!isset($body->plugin_url) ||
       gettype($body->plugin_url) != 'string') {
      throw new InvalidField('plugin_url');
   }

   // Quickly validating
   if (Plugin::where('xml_url', '=', $body->plugin_url)->count() > 0) {
      Tool::endWithJson([
          "error" => "That plugin XML URL has already been submitted."
      ]);
   }

   $xml = @file_get_contents($body->plugin_url);
   if (!$xml) {
      Tool::endWithJson([
          "error" => "We cannot fetch that URL."
      ]);
   }

   $xml = new ValidableXMLPluginDescription($xml);
   if (!$xml->isValid()) {
      Tool::endWithJson([
          "error" => "Unreadable/Non validable XML.",
          "details" => $xml->errors
      ]);
   }
   $xml = $xml->contents;

   if (Plugin::where('key', '=', $xml->key)->count() > 0) {
      Tool::endWithJson([
          "error" => "Your XML describe a plugin whose key already exists in our database."
      ]);
   }

   $plugin = new Plugin;
   $plugin->xml_url = $body->plugin_url;
   $plugin->date_added = DB::raw('NOW()');
   $plugin->active = false;
   $plugin->download_count = 0;
   $plugin->save();

   // mail($recipients,
   //      $msg_alerts_settings['subject_prefix'].'[PLUGIN SUBMISSION] '.$xml->name.' ('.$xml->key.')',
   //      'A new plugin "'.$xml->name.'" with key "'.$xml->key.'" has been submitted and is awaiting to be verified. It has db id #'.$plugin->id,
   //      "From: GLPI Plugins <plugins@glpi-project.org>");
   $mailer = new Mailer;
   $mailer->sendMail('plugin_submission.html', Tool::getConfig()['msg_alerts']['local_admins'],
                     '[PLUGIN SUBMISSION] '.$xml->name. ' ('.$xml->key.')',
                     ['plugin_xml' => (array)$xml]);
>>>>>>> now using \API\Core\Mailer in Plugin::submit endpoint


   Tool::endWithJson([
      "success" => true
   ]);
});

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
