<?php

namespace API\Core;

// Those are the Exception Classes
// that are thrown all across the app
// controllers (endpoints)
// when we want to reject a request
// with an error response
use \API\Exception\ErrorResponse;
use \API\Exception\InvalidRecaptcha;
use \League\OAuth2\Server\Exception\OAuthException;
use \ReCaptcha\ReCaptcha;

/**
 * this class has very wide scope,
 * being a toolbox static class
 * used in every endpoint
 */
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
         http_response_code($code);
      }

      // Parsing Accept Header
      if ($_SERVER['HTTP_ACCEPT'] == 'application/json') {
         $acceptHtml = false;
      }
      else {
         $acceptHtml = true;
      }

      if ($acceptHtml) { // if Accept header is passed
        // we use the Javascript prettyPrint
        $app->response->headers->set('Content-Type', 'text/html');
        $payload = preg_replace('/\$code/',
                             htmlentities(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
                             self::$prettyJSONTemplate);
         $app->halt($code, $payload);
      } else { // else we just output
        $app->response->headers->set('Content-Type', 'application/json');
        $app->halt($code, json_encode($payload));
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
    * This decorates a lambda function that
    * serves as an endpoint, it makes
    * use of try{}catch{} to generate
    * various responses
    */
   public static function makeEndpoint($callable) {
      $decoratedEndpoint = function() use($callable) {
         $args = func_get_args();
         try {
            try {
               call_user_func_array($callable, $args);
            }
            catch (\Exception $e) {
               global $app;
               if (!preg_match('/^API\\\\Exception/', get_class($e))) {
                  switch (get_class($e)) {
                     case 'League\OAuth2\Server\Exception\InvalidRequestException':
                        $parameter = explode('"', $e->getMessage())[1];
                        switch ($parameter) {
                           case 'client_secret':
                              $clientId = null;
                              $clientSecret = null;
                              if ($app->request->post('client_id')) {
                                 $clientId = $app->request->post('client_id');
                              }
                              if ($app->request->post('client_secret')) {
                                 $clientSecret = $app->request->post('client_secret');
                              }
                              throw new \API\Exception\ClientSecretError($clientId, $clientSecret);
                              break;
                           case 'access token':
                              throw new \API\Exception\NoAccessToken;
                              break;
                        }
                        break;
                     case 'League\OAuth2\Server\Exception\InvalidClientException':
                        $clientId = null;
                        $clientSecret = null;
                        if ($app->request->post('client_id')) {
                           $clientId = $app->request->post('client_id');
                        }
                        if ($app->request->post('client_secret')) {
                           $clientSecret = $app->request->post('client_secret');
                        }
                        throw new \API\Exception\ClientSecretError($clientId, $clientSecret);
                        break;
                     case 'League\OAuth2\Server\Exception\AccessDeniedException':
                        if (isset($app->request->headers['authorization'])) {
                           $token = $app->request->headers['authorization'];
                        } else {
                           $token = null;
                        }
                        throw new \API\Exception\AccessDenied($token);
                        break;
                     case 'League\OAuth2\Server\Exception\InvalidRefreshException':
                        if ($app->request->post('refresh_token')) {
                           $token = $app->request->post('refresh_token');
                        } else {
                           $token = null;
                        }
                        throw new \API\Exception\InvalidRefreshToken($token);
                        break;
                     case 'League\OAuth2\Server\Exception\InvalidScopeException':
                        $parameter = explode('"', $e->getMessage())[1];
                        throw new \API\Exception\InvalidScope($parameter);
                        break;
                     case 'League\OAuth2\Server\Exception\InvalidCredentialsException':
                        throw new \API\Exception\InvalidCredentials(($app->request->post('username') ? $app->request->post('username') : null),
                                                                   ($app->request->post('password') ? strlen($app->request->post('password')) : 0));
                        break;
                     case 'Slim\Exception\Stop':
                       // we just let SLim halt() the app
                       break;
                     default:
                        // ServiceError exception will use
                        // file, line and exception message as private
                        // data and send the simple code (without
                        // critical information to the user)
                        $serviceError = new \API\Exception\ServiceError($e->getFile(),$e->getLine(),$e->getMessage());
                        throw $serviceError;
                        break;
                  }
               } else {
                  throw $e;
               }
            }

         }
         catch (ErrorResponse $e) {
            $e->log();
            return Tool::endWithJson([
               "error" => $e->getRepresentation(true)
            ], $e->httpStatusCode);
         }

      };
      return $decoratedEndpoint;
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
    * This is used in endpoints to ask recaptcha
    * to validate a specific recaptcha response
    */
   public static function assertRecaptchaValid($recaptcha_response) {
      $recaptcha = new ReCaptcha(Tool::getConfig()['recaptcha_secret']);
      if (!$recaptcha->verify($recaptcha_response)
                     ->isSuccess()) {
         throw new InvalidRecaptcha;
      }
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

   /**
    * This count the items returned by a SQL query
    */
   public static function preCountQuery($queryBuilder) {
      $qb = clone $queryBuilder;
      return \Illuminate\Database\Capsule\Manager::table(
                  \Illuminate\Database\Capsule\Manager::raw(
                     "({$qb->toSql()}) as sub"))
                        ->mergeBindings($qb->getQuery())
                        ->count();
   }

   /**
    * Generates a random sha1
    */
   public static function randomSha1() {
      $characters  = '0123456789'; // numeric
      $characters .= 'abcdefghijklmnopqrstuvwxyz'; // lowercase
      $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // uppercase
      $len = strlen($characters);
      $out = '';
      for ($i = 0; $i < 20; $i++) {
         $out .= $characters[rand(0, $len - 1)];
      }
      return sha1($out . date('Ymdhms'));
   }
}
