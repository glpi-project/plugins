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
use \Illuminate\Database\Capsule\Manager as DB;

use \API\Model\User;
use \API\Model\UserExternalAccount;

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

/**
 * Login endpoint
 */
// $login = function() use ($app) {
//    $body = Tool::getBody();
//    $ok = null;

//    if (!isset($body->login) ||
//        !isset($body->password)) {
//       $ok = false;
//    } else {
//       $user = User::where(function($q) use($body) {
//                      return $q->where('email', '=', $body->login)
//                               ->orWhere('username', '=', $body->login);
//                   });

//       $count = $user->count();
//       if ($count < 1) {
//          $ok = false;
//       }
//       if ($count > 1) {
//          Tool::log('Dangerous, query result count > 1 when user tried'.
//                    ' to log with username "'.$body->login.'" '.
//                    'and password "'.$body->password.'"');
//          $ok = false;
//       } elseif ($count == 0) {
        
//       } else {
//          $user = $user->first();
//          var_dump($user);
//          if ($user->assertPasswordIs($body->password)) {
//             $ok = true;
//          }
//       }
//    }

//    if (!$ok) {
//       return Tool::endWithJson([
//          "error" => "Wrong email/username and/or password"
//       ], 400);
//    } else {
//       // Deliver JWT
//       return Tool::endWithJson([
//          "error" => "You are successfully logged in"
//       ], 200);
//    }
// };

/**
 * OAuth callback
 */
$oAuthCallback = function($service) use($app) {
   $oAuth = new API\Core\OAuthClient($service);
   $token = $oAuth->getAuthorization($app->request->get('code'));
   $oauth_user = $oAuth->user->toArray();

   $known = UserExternalAccount::where('token', '=', $token)
                               ->where('service', '=', $service)
                               ->first();

   if (sizeof($known) > 0) {
      $user = $known->user;

      echo 'known user !';
   } else {
      $user = new User;
      $user->realname = $oauth_user['name'];
      $user->username = $oauth_user['login'];
      $user->email = $oAuth->getEmail($token);
      $user->location = $oauth_user['location'];
      $user->save();

      $oauth_token = new UserExternalAccount;
      $oauth_token->token = $token;
      $oauth_token->service = $service;

      $user->tokens()->save($oauth_token);
   }

};

// HTTP REST Map
$app->post('/user', $register);
$app->post('/user/login', $login);
$app->get('/oauthcallback/:service', $oAuthCallback);

$app->options('/user', function() {});
$app->options('/user/login', function() {});
$app->options('/oauthcallback/:service', function() {});