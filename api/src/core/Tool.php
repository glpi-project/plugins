<?php

namespace API\Core;

class Tool {
   /**
    * You can use this method to
    * end with a JSON value from
    * an endpoint.
    */
   public static function endWithJson($json_value, $code = 200) {
      global $app;
      if (self::$paginationMode) {
         $app->response->headers['content-range']  = self::$paginationRange['startindex'];
         $app->response->headers['content-range'] .= '-' . (self::$paginationRange['startindex'] + sizeof($json_value));
         $app->response->headers['content-range'] .= '/' . self::$paginationMax;
         $app->response->headers['accept-range']   = 'model '.self::$paginationMax;
         if (sizeof($json_value) == self::$paginationMax) {
            $app->response->setStatus(200);
         } else {
            $app->response->setStatus(206);
         }
      } else {
         $app->response->setStatus($code);
      }
      $app->response->headers->set('Content-Type', 'application/json');
      echo json_encode($json_value);
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
    * Method to fetch X-Range header in a
    * convenient way
    */
   public static function getRangeHeaders() {
      global $app;
      $default = [
         "startindex" => 0,
         "endindex" => Tool::getConfig()['default_max_number_of_resources']
      ];

      if (!$app->request->headers['x-range'])
         return $default;

      $start_end = explode('-', $app->request->headers['x-range']);
      if (sizeof($start_end) != 2)
         return $default;

      return [
         "startindex" => (int)$start_end[0],
         "endindex" => (int)$start_end[1]
      ];
   }

   public static $paginationMode = false;
   public static $paginationRange = null;
   public static $paginationMax = null;

   /**
    * Returns a base Eloquent Model with skip()
    * and take() settings
    */
   public static function getCollectionPaginated($collection_name) {
      self::$paginationMode = true;
      $range_headers = Tool::getRangeHeaders();
      self::$paginationRange = $range_headers;
      $class_name = '\API\Model\\'.$collection_name;
      $model = new $class_name;
      self::$paginationMax = $model->count();
      $queryBuilder = $model->skip($range_headers['startindex'])
                     ->take($range_headers['endindex'] - $range_headers['startindex']);
      return $queryBuilder;
   }
}