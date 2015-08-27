<?php

namespace API\Core;

class Tool {

   /**
    * This template is used to deliver an API response
    * with syntaxic coloration of JSON, in case the
    * browser asks something else than pure
    * application/json (in other words, in case the
    * user directly points his browser to an API
    * endpoint)
    */
   public static $prettyJSONTemplate =
      '<!DOCTYPE html>'.
      '<html>'.
         '<head>'.
            '<title></title>'.
            '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.7/styles/github.min.css">'.
            '<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.7/highlight.min.js"></script>'.
            '<style>'.
               '.hljs '.
               '{'.
                  'word-break: break-word;'.
               '}'.
               'body'.
               '{'.
                  'padding: 0;'.
                  'margin: 0;'.
                  'background-color: #f8f8f8;'.
               '}'.
               'pre'.
               '{'.
                  'margin: 0;'.
                  'padding: 0;'.
               '}'.
               'pre code'.
               '{'.
                  'padding: 0 80px;'.
                  'max-width: 1000px;'.
                  'margin: 0 auto;'.
                '}'.
            '</style>'.
         '</head>'.
         '<body>'.
            '<pre><code class="json">$code</code></pre>'.
            '<script>hljs.initHighlightingOnLoad();</script>'.
         '</body>'.
      '</html>';

   /**
    * You can use this method to
    * end with a JSON value from
    * an endpoint.
    */
   public static function endWithJson($_payload, $code = 200) {
      global $app;

      // Handling special case of PaginatedCollection
      if ($_payload instanceof \API\Core\PaginatedCollection) {
         $_payload->setStatus($app->response);
         $_payload->setHeaders($app->response);
         $payload = $_payload->get($app->request->headers['x-range']);
      // Or serialize the payload as is
      } else {
         $payload = &$_payload;
         $app->response->setStatus($code);
      }

      // Parsing Accept Header
      if ($_SERVER['HTTP_ACCEPT'] == 'application/json') {
         $acceptHtml = false;
      }
      else {
         $acceptHtml = true;
      }

      if ($acceptHtml) {
        $app->response->headers->set('Content-Type', 'text/html');
        $code = preg_replace('/\$code/', htmlentities(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)), self::$prettyJSONTemplate);
        echo($code);
      } else {
        $app->response->headers->set('Content-Type', 'application/json');
        echo json_encode($payload);
      }
   }

   /**
    * This methods logs to the path
    * defined in the virtualhost
    * as error log
    */
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

   public static function preCountQuery($queryBuilder) {
      $qb = clone $queryBuilder;
      return \Illuminate\Database\Capsule\Manager::table(
                  \Illuminate\Database\Capsule\Manager::raw(
                     "({$qb->toSql()}) as sub"))
                        ->mergeBindings($qb->getQuery())
                        ->count();
   }

   public static function randomSha1() {
      $characters  = '0123456789';
      $characters .= 'abcdefghijklmnopqrstuvwxyz';
      $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $len = strlen($characters);
      $out = '';
      for ($i = 0; $i < 20; $i++) {
         $out .= $characters[rand(0, $len - 1)];
      }
      return sha1($out . date('Ymdhms'));
   }
}
