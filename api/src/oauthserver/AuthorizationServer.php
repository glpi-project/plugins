<?php

namespace API\OAuthServer;

use \API\OAuthServer\AccessTokenStorage;
use \API\OAuthServer\AuthCodeStorage;
use \API\OAuthServer\ClientStorage;
use \API\OAuthServer\ScopeStorage;
use \API\OAuthServer\SessionStorage;
use \League\OAuth2\Server\Grant\PasswordGrant;
use \League\OAuth2\Server\Grant\ClientCredentialsGrant;
use \League\OAuth2\Server\Grant\RefreshTokenGrant;
use \API\Model\User;
use \API\Exception\InvalidScope;

class AuthorizationServer extends \League\OAuth2\Server\AuthorizationServer {

   /**
    * This extended constructor is setting up
    * the underlying AuthorizationServer with
    * the grant types that GLPi Plugins support
    * on it's OAuth2 Framework
    */
   public function __construct() {
      parent::__construct();
      $this->setSessionStorage(OAuthHelper::getSessionStorage());
      $this->setAccessTokenStorage(OAuthHelper::getAccessTokenStorage());
      $this->setRefreshTokenStorage(OAuthHelper::getRefreshTokenStorage());
      $this->setClientStorage(OAuthHelper::getClientStorage());
      $this->setScopeStorage(OAuthHelper::getScopeStorage());
      $this->setAuthCodeStorage(new AuthCodeStorage());

      // Adding the password grant to able users to login by themselves
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

      $appGrant = new ClientCredentialsGrant;
      $this->addGrantType($appGrant);

      $refreshTokenGrant = new RefreshTokenGrant();
      $this->addGrantType($refreshTokenGrant);
   }

   /**
    * The primary use case of this firewall is to
    * limit the availability of requestable
    * scopes in order not to allow every possible
    * scope to client_credentials grant type
    * requests.
    */
   private function firewallOnScopes($grantType, $scopes) {
      $onlyForAuthed = ['user', 'user:externalaccounts', 'user:apps'];

      if ($grantType == 'client_credentials') {
         foreach ($onlyForAuthed as $scope) {
            if (in_array($scope, $scopes)) {
               throw new InvalidScope($scope);
            }
         }
      }
   }

   /**
    * This inherited method, from oauth2-server
    * aims to provide an access_token for users
    * when they request it. OAuth2-Server verifies
    * accuracy of credentials, and uses it's
    * storage-class based system which we implement
    * using eloquent models.
    *
    * This method also calls firewallOnScopes()
    * to ensure that an anonymous token is never
    * requested with scopes like "user" or so
    */
   public function issueAccessToken() {
      $grantType = $this->getRequest()->request->get('grant_type');
      $scopes = explode(' ', $this->getRequest()->request->get('scope'));

      $this->firewallOnScopes($grantType, $scopes);
      return parent::issueAccessToken();
   }
}