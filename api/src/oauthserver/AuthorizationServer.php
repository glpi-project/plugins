<?php

namespace API\OAuthServer;

use \API\OAuthServer\AccessTokenStorage;
use \API\OAuthServer\AuthCodeStorage;
use \API\OAuthServer\ClientStorage;
use \API\OAuthServer\ScopeStorage;
use \API\OAuthServer\SessionStorage;
use \League\OAuth2\Server\Grant\PasswordGrant;
use \API\Model\User;

class AuthorizationServer extends \League\OAuth2\Server\AuthorizationServer {

   public function __construct() {
      parent::__construct();
      $this->setSessionStorage(new SessionStorage());
      $this->setAccessTokenStorage(new AccessTokenStorage());
      //$authorizationServer->setRefreshTokenStorage(new RefreshTokenStorage);
      $this->setClientStorage(new ClientStorage());
      $this->setScopeStorage(new ScopeStorage());
      $this->setAuthCodeStorage(new AuthCodeStorage());

      $passwordGrant = new PasswordGrant();
      $passwordGrant->setVerifyCredentialsCallback(function($login, $password) {
         $ok = null;

         $user = User::where(function($q) use($login) {
            return $q->where('email', '=', $login)
                     ->orWhere('username', '=', $login);
         });

         $count = $user->count();
         if ($count < 1) {
            $ok = false;
         }
         if ($count > 1) {
            Tool::log('Dangerous, query result count > 1 when user tried'.
            ' to log with login "'.$login.'" '.
            'and password "'.$password.'"');
            $ok = false;
         } elseif ($count == 0) {

         } else {
            $user = $user->first();
            if ($user->assertPasswordIs($password)) {
               $ok = true;
               return $user->id;
            }
         }

         return $ok;
      });

      $this->addGrantType($passwordGrant);
   }

}