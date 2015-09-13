<?php

namespace API\OAuthServer;

use \API\Core\Tool;
use \League\OAuth2\Server\Exception\AccessDeniedException;
use \Illuminate\Database\Capsule\Manager as DB;
use League\OAuth2\Server\Util\SecureKey;

use \API\Model\User;
use \API\Model\AccessToken;
use \API\Model\RefreshToken;
use \API\Model\Session;
use \API\Model\Scope;

/**
 * This class helps retrieving part
 * of the OAuth subsystem as instances
 *
 * @todo : add methods to fetch singleton
 *         of authorization and resource
 *         servers
 */
class OAuthHelper {
   private static $accessTokenStorage = null;
   private static $refreshTokenStorage = null;
   private static $clientStorage = null;
   private static $scopeStorage = null;
   private static $sessionStorage = null;


   /**
    * Returns a singleton of the AccessTokenStorage
    */
   public static function getAccessTokenStorage() {
      if (!self::$accessTokenStorage) {
         self::$accessTokenStorage = new AccessTokenStorage;
      }
      return self::$accessTokenStorage;
   }

   /**
    * Returns a singleton of the AccessTokenStorage
    */
   public static function getRefreshTokenStorage() {
      if (!self::$refreshTokenStorage) {
         self::$refreshTokenStorage = new RefreshTokenStorage;
      }
      return self::$refreshTokenStorage;
   }

   /**
    * Returns a singleton of the ClientStorage
    */
   public static function getClientStorage() {
      if (!self::$clientStorage) {
         self::$clientStorage = new ClientStorage;
      }
      return self::$clientStorage;
   }

   /**
    * Returns a singleton of the ScopeStorage
    */
   public static function getScopeStorage() {
      if (!self::$scopeStorage) {
         self::$scopeStorage = new ScopeStorage;
      }
      return self::$scopeStorage;
   }

   /**
    * Returns a singleton of the SessionStorage
    */
   public static function getSessionStorage() {
      if (!self::$sessionStorage) {
         self::$sessionStorage = new SessionStorage;
      }
      return self::$sessionStorage;
   }

   /**
    * This function is useful to help
    * limit usage of a specific endpoint to
    * a list of specified scope, and at the same
    * time it requires the user-agent to pass it's
    * access token
    */
   public static function needsScopes(Array $scopes = []) {
      global $resourceServer;

      try {
         $resourceServer->isValidRequest();
         foreach ($scopes as $scope) {
            if (!$resourceServer->getAccessToken()->hasScope($scope)) {
               throw new AccessDeniedException();
            }
         }
      }
      catch (\League\OAuth2\Server\Exception\OAuthException $e) {
         Tool::endWithJson([
            "error" => $e->getMessage()
         ], $e->httpStatusCode);
         exit;
      }
   }

   /**
    * It creates an access token, a session, and links
    * scopes mentionned in $scopes to the session and
    * access token, it finally returns the new access token
    *
    * It associates the 'webapp' app
    */
   public static function createAccessTokenFromUserId($user_id, $scopes, $ttl = 3600) {
      $user = User::where('id', '=', $user_id)->first();

      if (!$user) {
         return false;
      }

      $session = new Session;
      $session->owner_type = 'user';
      $session->owner_id = $user->id;
      $session->app_id = 'webapp';
      $session->save();

      $accessToken = new AccessToken;
      $accessToken->session_id = $session->id;
      $accessToken->token = SecureKey::generate();
      $accessToken->expire_time = DB::raw('FROM_UNIXTIME('.($ttl + time()).')');
      $accessToken->save();

      foreach ($scopes as $_scope) {
         $scope = Scope::where('identifier', '=', $_scope)->first();
         if ($scope) {
            $session->scopes()->attach($scope);
            $accessToken->scopes()->attach($scope);
         }
      }

      $refreshToken = new RefreshToken;
      $refreshToken->access_token_id = $accessToken->id;
      $refreshToken->token = SecureKey::generate();
      $refreshToken->expire_time = DB::raw('FROM_UNIXTIME('.(604800 + time()).')');
      $refreshToken->save();

      return [
         "token" => $accessToken->token,
         "refresh_token" => $refreshToken->token,
         "ttl"   => $ttl
      ];
   }
}