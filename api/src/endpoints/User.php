<?php
/**
 * User
 *
 * This REST module hooks on
 * following URLs :
 *
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
$register = function() use ($app) {
   $body = Tool::getBody();
   $new_user = new User;

   if (!isset($body->username) ||
       strlen($body->username) < 4 ||
       strlen($body->username) > 28 ||
       preg_match('[^a-zA-Z0-9]', $body->username)) {
      return Tool::endWithJson([
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
      return Tool::endWithJson([
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
       strlen($body->location) < 4 ||
       preg_match('[^a-zA-Z0-9éè ]', $body->location)) {
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

   if (!isset($body->password) ||
       strlen($body->password) < 6 ||
       strlen($body->password) > 26) {
      return Tool::endWithJson([
         "error" => "Your password should have at least 6 characters, ".
                    "and a maximum of 26 characters"
      ], 400);
   }

   if (!isset($body->password_repeat) ||
       $body->password != $body->password_repeat) {
      return Tool::endWithJson([
         "error" => "Your password and password verification doesn't match"
      ], 400);
   } else {
      $new_user->setPassword($body->password);
   }

   $new_user->save();

   Tool::endWithJson([], 200);
};

$associateExternalAccount = function($service) use($app, $resourceServer) {
   $oAuth = new OAuthClient($service);
   $token = $oAuth->getAccessToken($app->request->get('code'));
   $authorizationServer = new AuthorizationServer();
   $data = [];

   if ($app->request->get('access_token')) {
      $resourceServer->isValidRequest(false);
      $alreadyAuthed = true;
      $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   } else {
      $alreadyAuthed = false;
   }

   $external_account_infos = $oAuth->getInfos($token);

   if ($alreadyAuthed) {
      $user = User::where('id', '=', $user_id)->first();
      if (!$user) {
         Tool::log('StrangeError : Session has unexisting user_id');
         $data['error'] = 'Service error';
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
         $data['error'] = 'You are already authed, and your '.$service.' account is already linked';
      }
   } else {
         // Creating the User account locally
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
            $user->location = $external_account_infos['location'];
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

         $session = new Session;
         $session->owner_type = 'user';
         $session->owner_id = $user->id;
         $session->app_id = 'webapp';
         $session->save();


         $accessToken = new AccessToken;
         $accessToken->session_id = $session->id;
         $accessToken->token = SecureKey::generate();
         $accessToken->expire_time = $authorizationServer->getAccessTokenTTL() + time();
         $accessToken->save();

         // Allowing the user scope for now
         $userScope = Scope::where('identifier', '=', 'user')->first();
         $session->scopes()->attach($userScope);
         $accessToken->scopes()->attach($userScope);

         $data['access_token'] = $accessToken->token;
         $data['access_token_expires_in'] = $authorizationServer->getAccessTokenTTL();
   }

   if (isset($data['error'])) {
      // Before we make
      if ($data['error'] == 'Service error') {
         $app->response->setStatus(500);
      } else {
         $app->response->setStatus(400);
      }
   }

   echo '<!DOCTYPE html><html><head></head><body><script type="text/javascript">'.
           'var data = \''.json_encode($data).'\'; var i = 0 ; var interval = setInterval(function(){  if (i == 250) {clearInterval(interval);} i++; window.postMessage(data, "*");}, 70);'.
        '</script></body>';
};

$authorize = function() use($app) {
  if (isset($_POST['client_id']) &&
      isset($_POST['grant_type']) &&
      $_POST['grant_type'] == 'password' &&
      $_POST['client_id'] == "webapp") {
    $password_webapp_auth = true;
  } else {
    $password_webapp_auth = false;
  }

  if ($password_webapp_auth) {
    $_POST['client_secret'] = Tool::getConfig()['oauth_webapp_secret'];
  }

  $authorizationServer = new AuthorizationServer();

  try {
    return Tool::endWithJson($authorizationServer->issueAccessToken(), 200);
  }
  catch (\League\OAuth2\Server\Exception\OAuthException $e) {
    return Tool::endWithJson([
      "error" => $e->getMessage()
    ], $e->httpStatusCode);
  }
  catch (\Exception $e) {
    Tool::log('PHP error, file '.$e->getFile().' , line '.$e->getLine().' : '.$e->getMessage());
    return Tool::endWithJson([
      "error" => "Service error"
    ], 500);
  }
};

$oauth_external_emails = function() use($app, $resourceServer) {
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
};

$profile_view = function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user']);

   $user_id = $resourceServer->getAccessToken()->getSession()->getOwnerId();
   $user = User::where('id', '=', $user_id)->first();

   Tool::endWithJson($user, 200);
};

$profile_edit = function() use($app) {

};

// HTTP REST Map
//$app->post('/user', $register);
//$app->post('/user/login', $login);

$app->get('/user', $profile_view);
$app->put('/user', $profile_edit);

$app->get('/oauth/available_emails', $oauth_external_emails);

$app->get('/oauth/associate/:service', $associateExternalAccount);
$app->post('/oauth/authorize', $authorize);

$app->options('/user', function() {});
$app->options('/oauth/authorize', function() {});
$app->options('/oauth/associate/:service', function() {});