<?php

namespace API\Core;

// Those are the Exception Classes
// that are thrown all across the app
// controllers (endpoints)
// when we want to reject a request
// with an error response
use \API\Exception\ErrorResponse;
use \API\Exception\InvalidRecaptcha;
use \API\Model\Plugin;
use \League\OAuth2\Server\Exception\OAuthException;
use \ReCaptcha\ReCaptcha;

/**
 * this class has very wide scope,
 * being a toolbox static class
 * used in every endpoint
 */
class Tool {
   public static function getPayload($_payload, $code = 200) {
      global $app;

      // Handling special case of PaginatedCollection
      if ($_payload instanceof \API\Core\PaginatedCollection) {
         $_payload->setStatus($app->response);
         $_payload->setHeaders($app->response);
         $payload = $_payload->get($app->request->headers['x-range']);
      } else {
         $payload = &$_payload;
         http_response_code($code);
      }

      return $payload;
   }


   /**
    * Method used to end with
    * a JSON value from
    * an endpoint.
    */
   public static function endWithJson($_payload, $code = 200) {
      global $app;

      $payload = self::getPayload($_payload);
      $app->response->headers->set('Content-Type', 'application/json');
      $app->halt($code, json_encode($payload));
   }

   /**
    * Method used to end with
    * a Rss value from
    * an endpoint.
    */
   public static function endWithRSS($_payload, $feed_title = '', $code = 200) {
      global $app;

      $plugins = self::getPayload($_payload);

      // retrieve app config
      $app_config = self::getConfig();
      $url = $app_config['client_url'];

      // create a feed
      $feed = new \Suin\RSSWriter\Feed();

      // create a channel
      $channel = new \Suin\RSSWriter\Channel();
      $channel
         ->title($feed_title)
         ->url($url)
         ->language('en-US')
         ->pubDate(time())
         ->lastBuildDate(time())
         ->ttl(60)
         ->appendTo($feed);


      foreach($plugins->toArray() as $current_plugin) {
         $plugin = Plugin::with('descriptions', 'authors', 'versions',
                                'screenshots', 'tags', 'langs')
                  ->short()
                  ->where('key', '=', $current_plugin['key'])
                  ->where('active', '=', 1)
                  ->first();

         if ($plugin) {
            $last_version = $plugin->getLatestVersion();
            $plugin = $plugin->toArray();

            // compute date
            $date = $plugin['date_updated'] != ""
                        ? $plugin['date_updated']
                        : ($plugin['date_added'] != ""
                           ? $plugin['date_added']
                           : date("Y-m-d"));

            // compute description
            $description = "";
            foreach($plugin['descriptions'] as $current_desc) {
               if ($current_desc['lang'] == 'en') {
                  $description = $current_desc['long_description'];
                  break;
               }
            }
            if (empty($description)) {
               $description = $plugin['descriptions'][0]['long_description'];
            }

            // find last version (the first in list)
            $version_num  = $version_compat = "";
            if ($last_version != NULL) {
               $last_version = $last_version->toArray();
               $version_num    = $last_version['num'];
               $version_compat = $last_version['compatibility'];
               $description.=  "<br />
                                <br /> Version: $version_num
                                <br /> Compatibility: $version_compat";
            }

            // add plugin to feed
            $item = new \Suin\RSSWriter\Item();
            $item
               ->title($plugin['name']." ".$version_num)
               ->description($description)
               ->contentEncoded($description)
               ->url($url.'/#/plugin/'.$plugin['key'])
               ->pubDate(strtotime($date))
               ->guid($plugin['name']."_".$date."_".$version_num, true)
               ->appendTo($channel);
         }
      }

      // render feed
      $app->halt($code, $feed->render());
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
                              throw (new \API\Exception\ClientSecretError($clientId, $clientSecret))
                                     ->childOf($e);
                              break;
                           case 'access token':
                              throw (new \API\Exception\NoAccessToken)
                                     ->childOf($e);
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
                        throw (new \API\Exception\ClientSecretError($clientId, $clientSecret))
                               ->childOf($e);
                        break;
                     case 'League\OAuth2\Server\Exception\AccessDeniedException':
                        if (isset($app->request->headers['authorization'])) {
                           $token = $app->request->headers['authorization'];
                        } else {
                           $token = null;
                        }
                        throw (new \API\Exception\AccessDenied($token))
                              ->childOf($e);
                        break;
                     case 'League\OAuth2\Server\Exception\InvalidRefreshException':
                        if ($app->request->post('refresh_token')) {
                           $token = $app->request->post('refresh_token');
                        } else {
                           $token = null;
                        }
                        throw (new \API\Exception\InvalidRefreshToken($token))
                               ->childOf($e);
                        break;
                     case 'League\OAuth2\Server\Exception\InvalidScopeException':
                        $parameter = explode('"', $e->getMessage())[1];
                        throw (new \API\Exception\InvalidScope($parameter))
                              ->childOf($e);
                        break;
                     case 'League\OAuth2\Server\Exception\InvalidCredentialsException':
                        throw (new \API\Exception\InvalidCredentials(($app->request->post('username') ? $app->request->post('username') : null),
                                                                                           ($app->request->post('password') ? strlen($app->request->post('password')) : 0)))
                               ->childOf($e);
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
                        // we don't use ->childOf() on this one,
                        // we're already provinding File, Line and Message
                        // of the Exception
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
