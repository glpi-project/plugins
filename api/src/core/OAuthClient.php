<?php

namespace API\Core;

class OAuthClient {
   private $provider;

   public function __construct($provider) {
      if (!isset(Tool::getConfig()['oauth'][$provider])) {
         Tool::log('OAuth settings not configured for provider \''.$provider.'\' in config.php');
         return Tool::endWithJson([], 500);
      }
      $config = Tool::getConfig()['oauth'][$provider];

      $this->service = $provider;
      if ($provider == 'github') {
         $this->provider = new \League\OAuth2\Client\Provider\Github([
            'clientId' => $config['clientId'],
            'clientSecret' => $config['clientSecret'],
            'redirectUri' => Tool::getConfig()['api_url']."/oauthcallback/github"
         ]);
      } else {
         Tool::endWithJson([
            "error" => "OAuth Provider ".$provider." doesn\'t exist"
         ], 500);
         exit;
      }
   }

   public function getAuthorization($code) {
      global $app;

      if (!$code) {
          // If we don't have an authorization code then get one
          $authUrl = $this->provider->getAuthorizationUrl([
               'scope' => ['user', 'user:email']
          ]);
          // $_SESSION['oauth2state'] = $this->provider->getState();
          $app->redirect($authUrl, 302);
          exit;
      }
      else {
          // Try to get an access token (using the authorization code grant)
          $token = $this->provider->getAccessToken('authorization_code', [
              'code' => $code
          ]);

          // Optional: Now you have a token you can look up a users profile data
          try {
              // We got an access token, let's now get the user's details
              $this->user = $this->provider->getResourceOwner($token);
          } catch (Exception $e) {
               Tool::endWithJson([
                  "error" => "Unhandled exception"
               ], 500);
               exit;
          }

          // Use this to interact with an API on the users behalf
          return $token->getToken();
      }
   }

   public function getEmail($token) {
      if ($this->service == 'github') {
         $guzzleClient = new \GuzzleHttp\Client();

         $emails = $guzzleClient->get('https://api.github.com/user/emails', [
            "headers" => [
             "Authorization" => 'token '.$token
            ]
         ]);
         $emails = json_decode((string)$emails->getBody());

         foreach($emails as $email) {
            if ($email->primary) {
               if ($email->verified) {
                $email = $email->email;
                break;
               }
            }
         }
         return $email;
      }
   }
} 