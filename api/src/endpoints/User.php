<?php
/**
 * User
 *
 * This REST module hooks on
 * following URLs :
 *
 */


use API\Core\Tool;
use API\Core\OAuthClient;
use API\Core\Mailer;
use API\Core\ValidableXMLPluginDescription;

use Illuminate\Database\Capsule\Manager as DB;

use API\Model\User;
use API\Model\UserExternalAccount;
use API\Model\Session;
use API\Model\AccessToken;
use API\Model\Scope;
use API\Model\App;
use API\Model\ValidationToken;
use API\Model\Plugin;
use API\Model\PluginWatch;

use API\Exception\UnavailableName;
use API\Exception\InvalidField;
use API\Exception\ExternalAccountAlreadyPaired;
use API\Exception\AlreadyWatched;
use API\Exception\NoCredentialsLeft;
use API\Exception\InvalidCredentials;
use API\Exception\InvalidXML;

use League\OAuth2\Server\Util\SecureKey;
use API\OAuthServer\AuthorizationServer;
use API\OAuthServer\OAuthHelper;

/**
 * Register a new user
 */
$register = Tool::makeEndpoint(function() use ($app) {
   $body = Tool::getBody();
   $new_user = new User;

   if (!isset($body->username) ||
       strlen($body->username) < 4 ||
       strlen($body->username) > 28 ||
       !preg_match('/^[a-zA-Z0-9]+$/', $body->username)) {
      throw new InvalidField('username');
   } else {
      if (User::where('username' , '=', $body->username)->first() != null) {
         throw new UnavailableName('User', $body->username);
      }
      $new_user->username = $body->username;
   }

   if (!isset($body->email) ||
      !filter_var($body->email, FILTER_VALIDATE_EMAIL)) {
      throw new InvalidField('email');
   } else {
      if (User::where('email', '=', $body->email)->first() != null) {
         throw new UnavailableName('Email', $body->email);
      }
      $new_user->email = $body->email;
   }

   if (!isset($body->realname) ||
       strlen($body->realname) < 4 ||
       preg_match('[^a-zA-Z0-9 ]', $body->realname)) {
      $new_user->realname = '';
   } else {
      $new_user->realname = $body->realname;
   }

   if (!isset($body->location) ||
       gettype($body->location) != 'string' ||
       strlen($body->location) < 1) {
      $new_user->location = '';
   } else {
      $new_user->location = $body->location;
   }

   if (!isset($body->website) ||
       strlen($body->website) < 4 ||
       !filter_var($body->email, FILTER_VALIDATE_URL)) {
      $new_user->website = '';
   } else {
      $new_user->website = $body->website;
   }

   if (!User::isValidPassword($body->password)) {
      throw new InvalidField('password');
   }
   $new_user->setPassword($body->password);

   $new_user->active = false;
   $new_user->save();

   $validationToken = new ValidationToken;
   $validationToken->token = SecureKey::generate();
   $validationToken->user_id = $new_user->id;
   $validationToken->save();

   $mailer = new Mailer;
   $mailer->sendMail('confirm_email.html', [$new_user->email] ,
                     'Please confirm your email account',
                     [
                        'user' => $new_user->toArray(),
                        'validation_token' => $validationToken->token
                     ]);
});

/**
 * Deletes the GLPi account on user request
 */
$user_delete_account = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user']);
   $body = Tool::getBody();

   $user = OAuthHelper::currentlyAuthed();

   // Ensures acceptable
   // password was given
   // so we don't do hash
   // enormous character
   // strings if evil.com
   // owner come visit
   // our systems
   if (!isset($body->password) ||
       gettype($body->password) != 'string' ||
       !User::isValidPassword($body->password)) {
      throw new InvalidField('password');
   }

   // Ensures given password is the good one
   if (!$user->assertPasswordIs($body->password)) {
      throw new InvalidCredentials($user->username, strlen($body->password));
   }

   // Delete the session and access_token
   $sessions = $user->sessions()->get();
   if (sizeof($sessions) > 0) {
      foreach ($sessions as $session) {
         if ($session->accessToken) {
            $session->accessToken->delete();
         }
         $session->delete();
      }
   }

   //@MonkeyPatch
   // delete all apps()
   $user->apps()->delete();

   // Finally delete the user
   $user->delete();
   $app->halt(200);
});

/**
 * Validate the email address for a manually registered user
 * (i.e: click on the link that is sent by mail)
 */
$user_validate_mail = Tool::makeEndpoint(function($_validationToken) use($app) {
  $validationToken = ValidationToken::where('token', '=', $_validationToken)->first();

  if ($validationToken) {
    $user = $validationToken->user;
    $user->active = true;
    $user->save();

   $accessToken = OAuthHelper::createAccessTokenFromUserId(
      $user->id,
      ['plugins', 'plugins:search', 'plugin:card', 'plugin:star',
       'plugin:submit', 'plugin:download', 'tags', 'tag', 'authors',
       'author', 'version', 'message', 'user', 'user:apps']
   );

   $mailer = new Mailer;
   $mailer->sendMail('confirm_account.html', [$user->email] ,
                     'Your email account is confirmed',
                     [
                        'user' => $user->toArray()
                     ]);
   $validationToken->delete();

   Tool::endWithJson([
      "access_token" => $accessToken['token'],
      "refresh_token" => $accessToken['refresh_token'],
      "expires_in" => $accessToken['ttl']
   ], 200);
  }
  else {
    throw new \API\Exception\InvalidValidationToken($_validationToken);
  }

});



/**
 * Returns the profile
 */
$profile_view = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user']);

   $user = OAuthHelper::currentlyAuthed();
   // Adding gravatar
   $user->gravatar = md5(strtolower(trim($user->email)));

   Tool::endWithJson($user, 200);
});

/**
 * endpoint to edit the main profile
 * of the logged user
 */
$profile_edit = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user']);

   $body = Tool::getBody();

   $user = OAuthHelper::currentlyAuthed();

   if (isset($body->email)) {
      $externalAccounts = $user->externalAccounts()->get();

      $email_found = false;
      foreach ($externalAccounts as $externalAccount) {
         $oAuth = new OAuthClient($externalAccount->service);
         $emails = $oAuth->getEmails($externalAccount->token);
         foreach ($emails as $_email) {
            if ($_email == $body->email) {
               $email_found = true;
               break;
            }
         }
         if ($email_found) {
            break;
         }
      }

      if ($email_found) {
         $access_token = $resourceServer->getAccessToken()->getId();
         OAuthHelper::grantScopesToAccessToken($access_token, ['plugins', 'plugins:search', 'plugin:card', 'plugin:star',
             'plugin:submit', 'plugin:download', 'tags', 'tag', 'authors',
             'author', 'version', 'message', 'user:externalaccounts',
             'user:apps']);
         $user->active = true;
         $user->email = $body->email;

         $mailer = new Mailer;
         $mailer->sendMail('external_account_mail_as_main.html', [$user->email] ,
                           'Email address validated by '.$externalAccount->service.' used as main email on GLPi Plugins',
                           ['user' => $user->toArray(),
                            'service' => $externalAccount->service]);
      }
   }

   if (isset($body->password) &&
       User::isValidPassword($body->password)) {
      $user->setPassword($body->password);
   }

   if (isset($body->realname) &&
       User::isValidRealname($body->realname)) {
      $user->realname = $body->realname;
   }

   if (isset($body->website) &&
       User::isValidWebsite($body->website)) {
      $user->website = $body->website;
   }

   $user->save();

   Tool::endWithJson($user, 200);
});

/**
 * Returns list of plugins for the current
 * user if he has an associated author.
 */
$user_plugins = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user', 'plugins']);

   $user = OAuthHelper::currentlyAuthed();

   Tool::endWithJson($user->plugins);
});

/**
 * Returns the list of plugins that the user
 * watch by key.
 */
$user_watchs = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user', 'plugins']);

   $user = OAuthHelper::currentlyAuthed();

   $plugins = [];
   foreach ($user->watchs()->get() as $watch) {
      $plugins[] = $watch->plugin->key;
   }

   Tool::endWithJson($plugins);
});

/**
 * Endpoint whose action is to declare the watch
 * of a plugin
 */
$user_add_watch = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user', 'plugins']);
   $body = Tool::getBody();

   $user = OAuthHelper::currentlyAuthed();

   if (!isset($body->plugin_key) ||
       gettype($body->plugin_key) != 'string') {
      throw new InvalidField('plugin_key');
   }

   $plugin = Plugin::where('key', '=', $body->plugin_key)->first();
   if (!$plugin) {
      throw new ResourceNotFound('Plugin', $body->plugin_key);
   }

   $already = $user->watchs()->where('plugin_id', '=', $plugin->id)->count();

   if ($already > 0) {
      throw new AlreadyWatched($body->plugin_key);
   }

   $watch = new PluginWatch;
   $watch->plugin_id = $plugin->id;
   $user->watchs()->save($watch);

   $app->halt(200);
});

/**
 * Endpoint whose action is to stop watching
 * a specific plugin
 */
$user_remove_watch = Tool::makeEndpoint(function ($key) use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user', 'plugins']);
   $body = Tool::getBody();

   $user = OAuthHelper::currentlyAuthed();

   $plugin = Plugin::where('key', '=', $key)->first();
   if (!$plugin) {
      throw new ResourceNotFound('Plugin', $key);
   }

   $watch = $user->watchs()->where('plugin_id', '=', $plugin->id)->first();

   if ($watch) {
      $watch->delete();
   } else {
      $app->halt(404);
   }

   $app->halt(200);
});

$user_search = Tool::makeEndpoint(function() {
   OAuthHelper::needsScopes(['users:search']);
   $body = Tool::getBody();

   if (!isset($body->search) ||
       gettype($body->search) != 'string') {
      throw new InvalidField('search');
   }
   $search = $body->search;

   $results = User::select(['username', 'realname'])
         ->where('username', 'LIKE', "%$search%")
         ->orWhere('realname', 'LIKE', "%$search%")
         ->orWhere('email', '=', $search)
         ->get();

   Tool::endWithJson($results);
});

// HTTP REST Map

// user profile related
$app->post('/user', $register);
$app->post('/user/delete', $user_delete_account);
$app->get('/user', $profile_view);
$app->put('/user', $profile_edit);
$app->get('/user/validatemail/:token', $user_validate_mail);

// user plugins related
$app->get('/user/plugins', $user_plugins);

// user watched plugins related
$app->get('/user/watchs', $user_watchs);
$app->post('/user/watchs', $user_add_watch);
$app->delete('/user/watchs/:key', $user_remove_watch);

// search trough user names
$app->post('/user/search', $user_search);

// options for CORS
$app->options('/user', function() {});
$app->options('/user/delete', function() {});
$app->options('/user/validatemail/:token', function($token) {});
$app->options('/user/watchs', function() {});
$app->options('/user/plugins', function() {});
$app->options('/user/search', function() {});