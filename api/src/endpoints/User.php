<?php
/**
 * User
 *
 * This REST module hooks on
 * following URLs :
 *
 */


use \API\Core\Tool;
use API\Core\OAuthClient;
use \Illuminate\Database\Capsule\Manager as DB;
use League\OAuth2\Server\Util\SecureKey;

use \API\Model\User;
use \API\Model\UserExternalAccount;
use \API\Model\Session;
use \API\Model\AccessToken;
use \API\Model\Scope;
use \API\Model\App;

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
       preg_match('[^a-zA-Z0-9]', $body->username)) {
      Tool::endWithJson([
         "error" => "Your username should have at least 4 characters, ".
                    "and a maximum of 28 characters, and it should ".
                    "contains only alphanumeric characters"
      ], 400);
   } else {
      $new_user->username = $body->username;
   }

   if (!isset($body->email) ||
       strlen($body->email) < 5 || // a@b.c
       strlen($body->email) > 255 ||
      !filter_var($body->email, FILTER_VALIDATE_EMAIL)) {
      Tool::endWithJson([
         "error" => "The email you specified isn't valid"
      ], 400);
   } else {
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
      Tool::endWithJson([
         "error" => "Your password should have at least 6 characters, ".
                    "and a maximum of 26 characters"
      ], 400);
   }

   if (!isset($body->password_repeat) ||
       $body->password != $body->password_repeat) {
      Tool::endWithJson([
         "error" => "Your password and password verification doesn't match"
      ], 400);
   } else {
      $new_user->setPassword($body->password);
   }

   $new_user->save();

   $accessToken = OAuthHelper::createAccessTokenFromUserId(
      $user->id,
      ['plugins', 'plugins:search', 'plugin:card', 'plugin:star',
       'plugin:submit', 'plugin:download', 'tags', 'tag', 'authors',
       'author', 'version', 'message', 'user', 'user:apps']
   );

   Tool::endWithJson([
      "access_token" => $accessToken['token'],
      "refresh_token" => $accessToken['refresh_token'],
      "access_token_expires_in" => $accessToken['ttl']
   ], 200);
});

/**
 * RPC that serves as a callback for the OAuth2
 * service
 */
$associateExternalAccount = Tool::makeEndpoint(function($service) use($app, $resourceServer) {
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
         $externalAccount = new UserExternalAccount;
         $externalAccount->external_user_id = $external_account_infos['id'];
         $externalAccount->token = $token;
         $externalAccount->service = $service;
         $user->externalAccounts()->save($externalAccount);

         $data['external_account_linked'] = true;
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
         $user->active = true;
         $user->email = $body->email;
      }
   }

   if (isset($body->password) &&
       User::isValidPassword($body->password)) {
      $user->setPassword($body->password);
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

   if (!$user->author) {
      Tool::endWithJson([], 200);
   }

   Tool::endWithJson($user->author->plugins()->get());
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
    Tool::endWithJson([
      "error" => "Unexisting app"
    ], 400);
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
    Tool::endWithJson([
      "error" => "You have to specify a correct name for your application"
    ], 400);
  } else if (App::where('user_id', '=', $user_id)
                ->where('name', '=', $body->name)->first() != null) {
    Tool::endWithJson([
      "error" => "You already have an app with that name"
    ], 400);
  }
  else {
    $app->name = $body->name;
  }

  if (isset($body->homepage_url)) {
    if (!App::isValidUrl($body->homepage_url)) {
      Tool::endWithJson([
        "error" => "The url you provided is not valid, better not specifying it or provide a correct one"
      ], 400);
    } else {
      $app->homepage_url = $body->homepage_url;
    }
  }

  if (isset($body->description)) {
    if (!App::isValidDescription($body->description)) {
      Tool::endWithJson([
        "error" => "The description you provided is too long"
      ], 400);
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
$app->get('/user/apps', $user_apps);
$app->get('/user/apps/:id', $user_app);
$app->post('/user/apps', $user_declare_app);

$app->get('/oauth/available_emails', $oauth_external_emails);
$app->get('/oauth/associate/:service', $associateExternalAccount);
$app->post('/oauth/authorize', $authorize);

$app->options('/user', function() {});
$app->options('/user/plugins', function() {});
$app->options('/user/apps', function() {});
$app->options('/user/apps/:id', function($id) {});
$app->options('/user/external_accounts', function() {});
$app->options('/oauth/authorize', function() {});
$app->options('/oauth/associate/:service', function() {});