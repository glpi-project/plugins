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
}