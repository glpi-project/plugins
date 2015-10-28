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
use \API\Model\Author;
use \API\Model\PluginStar;
use \ReCaptcha\ReCaptcha;
use \API\Core\ValidableXMLPluginDescription;
use \API\Exception\ResourceNotFound;
use \API\OAuthServer\OAuthHelper;
use \API\Exception\InvalidRecaptcha;
use \API\Exception\InvalidField;
use \API\Exception\LackAuthorship;
use \API\Exception\LackPermission;
use \API\Exception\InvalidXML;
use \API\Exception\DifferentPluginSignature;
use \API\Exception\RightAlreadyExist;
use \API\Exception\RightDoesntExist;
use \API\Exception\CannotDeleteAdmin;

/**
 * Fetching infos of a single plugin
 */
$single = Tool::makeEndpoint(function($key) use($app, $resourceServer) {
   OAuthHelper::needsScopes(['plugin:card']);

   $plugin = Plugin::with('descriptions', 'authors', 'versions', 'screenshots', 'tags', 'langs')
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

$single_authormode_view = Tool::makeEndpoint(function($key) use($app) {
   OAuthHelper::needsScopes(['plugin:card', 'user']);

   $user = OAuthHelper::currentlyAuthed();

   // get plugin
   $plugin = Plugin::with('descriptions', 'versions', 'screenshots', 'tags', 'permissions')
                  ->short()
                  ->withAverageNote()
                  ->withNumberOfVotes()
                  ->where('key', '=', $key)
                  ->where('active', '=', 1)
                  ->first();

   if (!$plugin) {
      throw new ResourceNotFound('Plugin', $key);
   }
   if (!$plugin->permissions()
               ->where('user_id', '=', $user->id)
               ->where(function($q) {
                  return $q->where('admin', '=', true)
                           ->orWhere('allowed_refresh_xml', '=', true)
                           ->orWhere('allowed_change_xml_url', '=', true);
               })
               ->first()) {
      throw new LackPermission('Plugin', $key, $user->username, 'authormode_view');
   }

   Tool::endWithJson([
      "card" => $plugin,
      "tags" => $plugin->tags()->get(),
      "statistics" => [
         "current_monthly_downloads" => 500,
         "current_weekly_downloads" => 250
      ]
   ]);
});

/**
 * Write-only endpoint to modify the card of
 * a plugin.
 *
 * Currently, this endpoint is only used to
 * modify the xml_url of the plugin.
 */
$single_authormode_edit = Tool::makeEndpoint(function($key) use($app) {
   OAuthHelper::needsScopes(['user', 'plugin:card']);

   $user = OAuthHelper::currentlyAuthed();

   // get plugin
   $plugin = Plugin::short()
                   ->withAverageNote()
                   ->withNumberOfVotes()
                   ->where('key', '=', $key)
                   ->where('active', '=', 1)
                   ->first();

   if (!$plugin) {
      throw new ResourceNotFound('Plugin', $key);
   }

   // verify
   //    + user has the admin flag on the plugin
   // or + user has the change_xml_url flag on the plugin
   // otherwise reject
   if (!$plugin->permissions()
               ->where(function($q) {
                  return $q->where('admin', '=', true)
                           ->orWhere('allowed_change_xml_url', '=', true);
               })
               ->where('user_id', '=', $user->id)
               ->first()) {
      throw new LackPermission('Plugin', $key, $user->username, 'change_xml_url');
   }

   $body = Tool::getBody();

   // User can change it's xml_url with this endpoint
   if (isset($body->xml_url)) {
      // We check if the URL is a correct URI
      if (!filter_var($body->xml_url, FILTER_VALIDATE_URL)) {
         throw new InvalidField;
      }

      // We check if we can fetch the file via HTTP
      $xml = @file_get_contents($body->xml_url);
      if (!$xml) {
         throw new InvalidXML('url', $body->xml_url);
      }

      // We check is the plugin meta-description is
      // complete, and without errors
      $xml = new ValidableXMLPluginDescription($xml);
      $xml->validate();
      $xml = $xml->contents;

      // Verifying the plugin key hasn't changed
      if ($xml->key != $plugin->key) {
         throw new DifferentPluginSignature('key');
      }

      // Verifying that at least the authors we knew
      // are present in the new xml, to do that,
      //   we compare what is in the xml
      $_xml_authors = (array)$xml->authors;
      $_xml_authors = $_xml_authors['author'];
      //   simplexml API returns a string if there
      //   is a single 'author' node in authors
      //   here
      if (gettype($_xml_authors) == 'string') {
         $_xml_authors = [$_xml_authors];
      }
      $xml_authors = [];
      //   while applying the fixKnownDuplicates patch
      foreach ($_xml_authors as $author) {
         $fkd_detected_authors = Author::fixKnownDuplicates($author);
         $xml_authors = array_merge($xml_authors, $fkd_detected_authors);
      }
      //   with what we already had
      $authors = $plugin->authors()->get();
      foreach ($authors as $author) {
         if (!in_array($author->name, $xml_authors)) {
            throw new DifferentPluginSignature('authors');
         }
      }

      $plugin->xml_url = $body->xml_url;
      $plugin->save();
   }

   $app->halt(200);
});

$plugin_view_permissions = Tool::makeEndpoint(function($key) use ($app) {
   OAuthHelper::needsScopes(['user', 'plugin:card']);
   $user = OAuthHelper::currentlyAuthed();

   $plugin = Plugin::where('key', '=', $key)->first();
   if (!$plugin) {
      throw new ResourceNotFound('Plugin', $key);
   }

   // verify user has the admin flag on the plugin
   // otherwise reject
   if (!$plugin->permissions()
               ->where('admin', '=', true)
               ->where('user_id', '=', $user->id)
               ->first()) {
      throw new LackPermission('Plugin', $key, $user->username, 'manage_permissions');
   }

   Tool::endWithJson($plugin->permissions);
});

$plugin_add_permission = Tool::makeEndpoint(function($key) use($app) {
   OAuthHelper::needsScopes(['user', 'plugin:card']);
   $user = OAuthHelper::currentlyAuthed();
   $body = Tool::getBody();

   $plugin = Plugin::where('key', '=', $key)->first();
   if (!$plugin) {
      throw new ResourceNotFound('Plugin', $key);
   }
   if (!$plugin->permissions()
               ->where('admin', '=', true)
               ->where('user_id', '=', $user->id)
               ->first()) {
      throw new LackPermission('manage_permissions', 'Plugin', $key);
   }

   if (!isset($body->username) ||
       gettype($body->username) != 'string') {
      throw new InvalidField('username');
   }

   // verify user has the admin flag on the plugin
   // otherwise reject
   $target_user = User::where('username', '=', $body->username)->first();
   if (!$target_user) {
      throw new ResourceNotFound('User', $body->username);
   }

   if ($plugin->permissions->find($target_user)) {
      throw new RightAlreadyExist($body->username, $plugin->key);
   }

   $plugin->permissions()->attach($target_user);
   $app->halt(200);
});

$plugin_delete_permission = Tool::makeEndpoint(function($key, $username) use($app) {
   OAuthHelper::needsScopes(['user', 'plugin:card']);
   $user = OAuthHelper::currentlyAuthed();

   // reject if plugin not found
   $plugin = Plugin::where('key', '=', $key)->first();
   if (!$plugin) {
      throw new ResourceNotFound('Plugin', $key);
   }

   // reject if target_user not found
   $target_user = $plugin->permissions()->where('username', '=', $username)->first();
   if (!$target_user) {
      throw new RightDoesntExist($username, $key);
   }

   // if the user is trying to delete a permission set
   // for another user,
   // we require him to be admin of the plugin
   if ($target_user->id != $user->id) { 
      $user = $plugin->permissions->find($user);
      if (!$user ||
          !$user->pivot->admin) {
         throw new LackPermission('Plugin', $key, $username, 'manage_permissions');
      }
   }

   if ($target_user->pivot->admin) {
      throw new CannotDeleteAdmin($plugin->key, $target_user->username);
   }

   // if everything is OK we clear
   // the permission
   $plugin->permissions()->detach($target_user);

   $app->halt(200);
});

$plugin_modify_permission = Tool::makeEndpoint(function($key, $username) use($app) {
   OAuthHelper::needsScopes(['user', 'plugin:card']);
   $body = Tool::getBody();
   $user = OAuthHelper::currentlyAuthed();

   if (!isset($body->right) ||
       gettype($body->right) != 'string' ||
       !in_array($body->right, ['allowed_refresh_xml', 'allowed_change_xml_url', 'allowed_notifications'])) {
      throw new InvalidField('right');
   }

   if (!isset($body->set) ||
       gettype($body->set) != 'boolean') {
      throw new InvalidField('set');
   }

   $plugin = Plugin::where('key', '=', $key)->first();
   if (!$plugin) {
      throw new ResourceNotFound('Plugin', $key);
   }

   // Verify user is admin on the plugin
   if (!$plugin->permissions()
              ->where('admin', '=', true)
              ->where('user_id', '=', $user->id)
              ->first()) {
      throw new LackPermission('manage_permissions', 'Plugin', $key);
   }

   // verify username has the username has a right
   // for the plugin
   if (!($target_user = $plugin->permissions()
                        ->where('username', '=', $username)
                        ->first())) {
      throw new RightDoesntExist($username, $plugin->key);
   }

   $target_user->pivot[$body->right] = ($body->set) ? $body->set : null;
   $target_user->pivot->save();
   $app->halt(200);
});

$plugin_refresh_xml = Tool::makeEndpoint(function($key) {
   // @todo, verify
   //   + user has the correct permission on the plugin
   //      + user has the admin flag on the plugin
   //   OR + user has the allowed_refresh_xml flag on the plugin
   // otherwise reject

   // @todo, answer
   //   + 200 OK with JSON Object containing keys :
   //     + xml_state  (passing, stuff, or so) 
   //   + LackPermission if rejected
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

   $plugin = Plugin::where('active', '=', true)->find($body->plugin_id);

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

   $user = OAuthHelper::currentlyAuthed();
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
      throw new UnavailableName('XML_URL', $body->plugin_url);
   }

   $xml = @file_get_contents($body->plugin_url);
   if (!$xml) {
      throw new InvalidXML('url', $ody->plugin_url);
   }

   $xml = new ValidableXMLPluginDescription($xml);
   $xml->validate();
   $xml = $xml->contents;

   if (Plugin::where('key', '=', $xml->key)->count() > 0) {
      throw new UnavailableName('Plugin', $xml->key);
   }

   $plugin = new Plugin;
   $plugin->xml_url = $body->plugin_url;
   $plugin->date_added = DB::raw('NOW()');
   $plugin->active = false;
   $plugin->download_count = 0;
   $plugin->save();
   $plugin->admins()->attach($user);

   $mailer = new Mailer;
   $mailer->sendMail('plugin_submission.html', Tool::getConfig()['msg_alerts']['local_admins'],
                     '[PLUGIN SUBMISSION] '.$xml->name. ' ('.$xml->key.')',
                     ['plugin_xml' => (array)$xml]);

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
$app->get('/plugin/:key', $single);
$app->get('/panel/plugin/:key', $single_authormode_view);
$app->post('/panel/plugin/:key', $single_authormode_edit);
$app->get('/plugin/:key/permissions', $plugin_view_permissions);
$app->post('/plugin/:key/permissions', $plugin_add_permission);
$app->delete('/plugin/:key/permissions/:username', $plugin_delete_permission);
$app->patch('/plugin/:key/permissions/:username', $plugin_modify_permission);

$app->options('/plugin',function(){});
$app->options('/plugin/popular',function(){});
$app->options('/plugin/trending',function(){});
$app->options('/plugin/updated',function(){});
$app->options('/plugin/new',function(){});
$app->options('/plugin/star',function(){});
$app->options('/plugin/:id',function($id){});
$app->options('/panel/plugin/:id',function($id){});
$app->options('/plugin/:key/permissions', function($key){});