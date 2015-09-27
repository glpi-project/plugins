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
use Illuminate\Database\Capsule\Manager as DB;
use League\OAuth2\Server\Util\SecureKey;

use \API\Model\User;
use \API\Model\UserExternalAccount;
use \API\Model\Session;
use \API\Model\AccessToken;
use \API\Model\Scope;
use \API\Model\App;
use \API\Model\ValidationToken;
use \API\Model\Plugin;
use \API\Model\PluginWatch;

use \API\Exception\UnavailableName;
use \API\Exception\InvalidField;
use \API\Exception\ExternalAccountAlreadyPaired;
use \API\Exception\AlreadyWatched;

use \API\OAuthServer\AuthorizationServer;
use \API\OAuthServer\OAuthHelper;

/**
 * Register a new user
 *
 * minimal infos are:
 *  + realname
 *  + username
 *  + password
 *  + email
 *  + location
 *  + website
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
                     'Please confirm your email account', ['stylesheet' => 'confirm_email.css',
                                             'user' => $new_user->toArray(),
                                             'validation_token' => $validationToken->token]);
});

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
                     'Your email account is confirmed', ['stylesheet' => 'confirm_account.css',
                                                         'user' => $user->toArray()]);
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
 * RPC that serves as a callback for the OAuth2
 * service
 */
$user_associate_external_account = Tool::makeEndpoint(function($service) use($app, $resourceServer) {
   $oAuth = new OAuthClient($service);
   $token = $oAuth->getAccessToken($app->request->get('code'));
   $data = [];


   if (isset($_COOKIE['access_token'])) {
      $alreadyAuthed = true;

      $accessToken = AccessToken::where('token', '=', $_COOKIE['access_token'])->first();
      setcookie('access_token', '', 1, '/');
      if (!$accessToken) {
         Tool::endWithJson([
            "error" => "You provided a wrong access_token via cookie"
         ]);
      } else {
         $user_id = $accessToken->session->user->id;
      }
   } else {
      $alreadyAuthed = false;
   }

   $external_account_infos = $oAuth->getInfos($token);

   if ($alreadyAuthed) {
      $user = User::where('id', '=', $user_id)->first();
      if (!$user) {
         Tool::log('warning: session has unexisting user_id '.$user_id);
         Tool::endWithJson([
            "error" => "Service error"
         ], 400);
      }

      $externalAccount = $user->externalAccounts()
               ->where('external_user_id', '=', $external_account_infos['id'])
               ->where('service', '=', $service)
               ->first();

      if (!$externalAccount) {
         if ($_externalAccount = UserExternalAccount::where('external_user_id', '=', $external_account_infos['id'])->first()) {
            $data['error'] = 'EXTERNAL_ACCOUNT_ALREADY_PAIRED';
         } else {
            $externalAccount = new UserExternalAccount;
            $externalAccount->external_user_id = $external_account_infos['id'];
            $externalAccount->token = $token;
            $externalAccount->service = $service;
            $user->externalAccounts()->save($externalAccount);

            $data['external_account_linked'] = true;
         }
      } else {
         $data['error'] = 'You are already authed, and that '.$service.' account is already linked';
      }
   } else {
      $externalAccount = UserExternalAccount::where('external_user_id', '=', $external_account_infos['id'])->first();

      // If we know that external account
      if ($externalAccount) {
         $user = $externalAccount->user;

         $accessToken = OAuthHelper::createAccessTokenFromUserId(
            $user->id,
            ['plugins', 'plugins:search', 'plugin:card', 'plugin:star',
             'plugin:submit', 'plugin:download', 'tags', 'tag', 'authors',
             'author', 'version', 'message', 'user', 'user:externalaccounts',
             'user:apps']
         );
      }
      else { // Else we're creating a new
         // local account, and are associating the external one
         // to that new local one
         $user = new User;
         $user->active = 0; // Notice it is created as non active
         $user->username = $external_account_infos['username'];
         if (isset($external_account_infos['realname'])) {
            $user->realname = $external_account_infos['realname'];
         }
         if (isset($external_account_infos['location'])) {
            $user->location = $external_account_infos['location'];
         }
         if (isset($external_account_infos['website'])) {
            $user->website = $external_account_infos['website'];
         }
         $user->save();
         $data['account_created'] = true;

         // Associating external user account
         $externalAccount = new UserExternalAccount;
         $externalAccount->external_user_id = $external_account_infos['id'];
         $externalAccount->token = $token;
         $externalAccount->service = $service;
         $user->externalAccounts()->save($externalAccount);
         $data['external_account_linked'] = true;

         $accessToken = OAuthHelper::createAccessTokenFromUserId(
            $user->id,
            ['user']
         );
      }
      $data['access_token'] = $accessToken['token'];
      $data['refresh_token'] = $accessToken['refresh_token'];
      $data['access_token_expires_in'] = $accessToken['ttl'];
   }

   if (isset($data['error'])) {
      if ($data['error'] == 'Service error') {
         $app->response->status(500);
      } else {
         $app->response->status(400);
      }
   }

   echo '<!DOCTYPE html><html><head></head><body><script type="text/javascript">'.
           'var data = \''.json_encode($data).'\'; var i = 0 ; var interval = setInterval(function(){  if (i == 300) {clearInterval(interval);} i++; window.postMessage(data, "*");}, 750);'.
        '</script></body></html>';
});

/**
 * Authorize an user, providing him an
 * access token
 */
$authorize = Tool::makeEndpoint(function() use($app) {
   if (isset($_POST['grant_type']) &&
       isset($_POST['client_id']) &&
       $_POST['client_id'] == 'webapp') {
      if (!isset($_POST['client_secret'])) {
        $_POST['client_secret'] = '';
      }
   }

   $authorizationServer = new AuthorizationServer();

   Tool::endWithJson($authorizationServer->issueAccessToken(), 200);
});

/**
 * Return the complete list of external accounts for an
 * authentified user
 */
$user_external_accounts = Tool::makeEndpoint(function() use ($app, $resourceServer) {
   OAuthHelper::needsScopes(['user:externalaccounts']);

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   if (!$user) {
      Tool::endWithJson(null, 401);
   }

   $external_accounts = $user->externalAccounts()->get();
   Tool::endWithJson($external_accounts, 200);
});

/**
 * Returns the complete list of emails
 * that are available through the
 * external accounts
 */
$oauth_external_emails = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user']);

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   $externalAccounts = $user->externalAccounts()->get();

   $emails = [];
   foreach ($externalAccounts as $externalAccount) {
      $oAuth = new OAuthClient($externalAccount->service);
      $_emails = $oAuth->getEmails($externalAccount->token);
      foreach ($_emails as $email) {
         $emails[] = ["email" => $email,
                      "service" => $externalAccount->service];
      }
   }

   Tool::endWithJson($emails);
});

/**
 * Returns the profile
 */
$profile_view = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user']);

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   Tool::endWithJson($user, 200);
});

/**
 * endpoint to edit the main profile
 * of the logged user
 */
$profile_edit = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user']);

   $body = Tool::getBody();

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

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

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   if (!$user) {
      throw new ResourceNotFound('User', $user_id);
   }

   if (!$user->author) {
      Tool::endWithJson([], 200);
   }

   Tool::endWithJson($user->author->plugins()->get());
});

$user_watchs = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user', 'plugins']);

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   if (!$user) {
      throw new ResourceNotFound('User', $user_id);
   }

   $plugins = [];
   foreach ($user->watchs()->get() as $watch) {
      $plugins[] = $watch->plugin->key;
   }

   Tool::endWithJson($plugins);
});

$user_add_watch = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user', 'plugins']);
   $body = Tool::getBody();

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   if (!$user) {
      throw new ResourceNotFound('User', $user_id);
   }

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

$user_remove_watch = Tool::makeEndpoint(function ($key) use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user', 'plugins']);
   $body = Tool::getBody();

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   if (!$user) {
      throw new ResourceNotFound('User', $user_id);
   }

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

$user_apps = Tool::makeEndpoint(function() use($app, $resourceServer) {
  OAuthHelper::needsScopes(['user', 'user:apps']);

  $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
  $user = User::where('id', '=', $user_id)->first();

  Tool::endWithJson($user->apps()->get());
});

$user_app = Tool::makeEndpoint(function($id) use($app, $resourceServer) {
  OAuthHelper::needsScopes(['user', 'user:apps']);

  $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
  $user = User::where('id', '=', $user_id)->first();

  $app = $user->apps()->where('id', '=', $id)->first();

  if (!$app) {
      throw new \API\Exception\ResourceNotFound('app', $id);
  }

  Tool::endWithJson($app);
});

$user_declare_app = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user', 'user:apps']);
   $body = Tool::getBody();

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   $app = new App;

   if (!isset($body->name) || !App::isValidName($body->name)) {
      throw new \API\Exception\InvalidField('name');
   } else if (App::where('user_id', '=', $user_id)
                 ->where('name', '=', $body->name)->first() != null) {
      throw new \API\Exception\UnavailableName('app', $name);
   }
   else {
     $app->name = $body->name;
   }

   if (isset($body->homepage_url)) {
     if (!App::isValidUrl($body->homepage_url)) {
       throw new \APi\Exception\InvalidField('url');
     } else {
       $app->homepage_url = $body->homepage_url;
     }
   }

   if (isset($body->description)) {
     if (!App::isValidDescription($body->description)) {
       throw new \API\Exception\InvalidField('description');
     } else {
       $app->description = $body->description;
     }
   }

   // If everything went ok
   $app->setRandomClientId();
   $app->setRandomSecret();

   // Then save
  $user->apps()->save($app);
});

// HTTP REST Map

$app->post('/user', $register);
$app->get('/user', $profile_view);
$app->put('/user', $profile_edit);
$app->get('/user/external_accounts', $user_external_accounts);
$app->get('/user/plugins', $user_plugins);
$app->get('/user/watchs', $user_watchs);
$app->post('/user/watchs', $user_add_watch);
$app->delete('/user/watchs/:key', $user_remove_watch);
$app->get('/user/apps', $user_apps);
$app->get('/user/validatemail/:token', $user_validate_mail);
$app->get('/user/apps/:id', $user_app);
$app->post('/user/apps', $user_declare_app);

$app->get('/oauth/available_emails', $oauth_external_emails);
$app->get('/oauth/associate/:service', $user_associate_external_account);
$app->post('/oauth/authorize', $authorize);

$app->options('/user', function() {});
$app->options('/user/plugins', function() {});
$app->options('/user/apps', function() {});
$app->options('/user/apps/:id', function($id) {});
$app->options('/user/external_accounts', function() {});
$app->options('/oauth/authorize', function() {});
$app->options('/oauth/associate/:service', function() {});