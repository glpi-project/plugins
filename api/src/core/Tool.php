<?php

namespace API\Core;

class Tool {
   /**
    * You can use this method to
    * end with a JSON value from
    * an endpoint.
    */
   public static function endWithJson($_payload, $code = 200) {
      global $app;
      if ($_payload instanceof \API\Core\PaginatedCollection) {
         $_payload->setStatus($app->response);
         $_payload->setHeaders($app->response);
         $payload = $_payload->get($app->request->headers['x-range']);
      } else {
         $payload = &$_payload;
         $app->response->setStatus($code);
      }
      $app->response->headers->set('Content-Type', 'application/json');
      echo json_encode($payload);
   }

   public static function log($v) {
      $v = trim($v);
      error_log('[glpi-plugin-directory] ' . $v, 0);
   }

   /**
    * You can use this method in
    * an endpoint to get the value
    * of the JSON body being given
    * with a request
    */
   public static function getBody() {
      global $app;
      $json = $app->request->getBody();
      $json = json_decode($json);
      return $json;
   }

   /**
    * This method is used by update.php
    * to generate a slug from a utf-8
    * string which helps having clean
    * url-friendly identifiers for tags
    */
   public static function getUrlSlug($free_string) {
      $free_string = strtolower($free_string);
      $free_string = mb_ereg_replace ( '[^a-z]' , '-' , $free_string);
      return $free_string;
   }

   /**
    * This method is a helper to get the
    * user specified current default
    * language for text
    */
   public static function getRequestLang() {
      global $app;
      if ($app->request->headers['x-lang']) {
         if (in_array($app->request->headers['x-lang'],
                       ['fr','en','es'])) {
            return $app->request->headers['x-lang'];
         }
      }
      return 'en';
   }

   /**
    * This method returns the configuration array
    */
   public static $config = null;
   public static function getConfig() {
      if (!self::$config) {
         require dirname(__FILE__) . '/../../config.php';
         self::$config = $config;
      }
      return self::$config;
   }

   /**
    * Shortcut to get a PaginatedCollection instance
    */
   public static function paginateCollection($queryBuilder) {
      return  new \API\Core\PaginatedCollection($queryBuilder);
   }
}