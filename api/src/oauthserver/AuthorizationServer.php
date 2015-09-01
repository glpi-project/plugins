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
      $this->setSessionStorage(OAuthHelper::getSessionStorage());
      $this->setAccessTokenStorage(OAuthHelper::getAccessTokenStorage());
      //$authorizationServer->setRefreshTokenStorage(new RefreshTokenStorage);
      $this->setClientStorage(OAuthHelper::getClientStorage());
      $this->setScopeStorage(OAuthHelper::getScopeStorage());
      $this->setAuthCodeStorage(new AuthCodeStorage());

      $passwordGrant = new PasswordGrant();
      $passwordGrant->setVerifyCredentialsCallback(function($login, $password) {
         $user = User::where(function($q) use($login) {
            return $q->where('email', '=', $login)
                     ->orWhere('username', '=', $login);
         });

         $count = $user->count();
         if ($count < 1) {
            return false;
         }
         if ($count > 1) {
            Tool::log('Dangerous, query result count > 1 when user tried'.
            ' to log with login "'.$login.'" '.
            'and password "'.$password.'"');
            return false;
         } elseif ($count == 0) {
            return false;
         } else {
            $user = $user->first();
            if ($user->assertPasswordIs($password)) {
               return $user->id;
            } else {
               return false;
            }
         }
      });

      $this->addGrantType($passwordGrant);
   }

}