<?php

namespace API\OAuthServer;

class OAuthHelper {
   private static $accessTokenStorage = null;
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
         return $resourceServer->isValidRequest();
      }
      catch (\League\OAuth2\Server\Exception\OAuthException $e) {
         self::endWithJon([
            "error" => $e->getMessage()
         ], $e->httpStatusCode);
         exit;
      }
   }
}