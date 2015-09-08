<?php

namespace API\Core;

class OAuthClient {
   private $provider;

   /**
    * Prepare the object to retrieve access token for
    * specific driver
    */
   public function __construct($provider) {
      if (!isset(Tool::getConfig()['oauth'][$provider])) {
         Tool::log('OAuth settings not configured for provider \''.$provider.'\' in config.php');
         Tool::endWithJson([], 500);
         exit;
      }
      $config = Tool::getConfig()['oauth'][$provider];

      $this->service = $provider;
      if ($provider == 'github') {
         $this->provider = new \League\OAuth2\Client\Provider\Github([
            'clientId' => $config['clientId'],
            'clientSecret' => $config['clientSecret']
         ]);
      } else {
         Tool::endWithJson([
            "error" => "OAuth Provider ".$provider." doesn\'t exist"
         ], 500);
         exit;
      }
   }

   /**
    * Once the object is instanciated this
    * method can be used to retrieve the
    * access token with an authorization code
    */
   public function getAccessToken($code) {
      global $app;

      if (!$code) {
         Tool::endWithJson([
            "error" => "There is no authorization code provided"
         ], 400);
         exit;
      }
      else {
          // Try to get an access token (using the authorization code grant)
          $token = $this->provider->getAccessToken('authorization_code', [
              'code' => $code
          ]);

          return $token->getToken();
      }
   }

   /**
    * This methods fetches the infos
    * provided by the external accounts
    */
   public function getInfos($token) {
      $guzzleClient = new \GuzzleHttp\Client();

      if ($this->service == 'github') {
         $user = $guzzleClient->get('https://api.github.com/user', [
            "headers" => [
               "Authorization" => "Bearer ".$token
            ]
         ]);
         $user = json_decode((string)$user->getBody());

         $out = [];

         if (isset($user->login)) {
            $out['username'] = $user->login;
         }

         if (isset($user->id)) {
            $out['id'] = $user->id;
         }

         if (isset($user->location)) {
            $out['location'] = $user->location;
         }

         if (isset($user->name)) {
            $out['realname'] = $user->name;
         }

         if (isset($user->blog)) {
            $out['website'] = $user->blog;
         }

         return $out;
      }
   }

   /**
    * This method can fetch the emails associated with
    * the external account and returns only the one
    * that are verified
    */
   public function getEmails($token) {
      $guzzleClient = new \GuzzleHttp\Client();

      if ($this->service == 'github') {
         $emails = $guzzleClient->get('https://api.github.com/user/emails', [
            "headers" => [
             "Authorization" => 'Bearer '.$token
            ]
         ]);
         $_emails = json_decode((string)$emails->getBody());

         $emails = [];
         foreach($_emails as $email) {
            if ($email->verified) {
             $email = $email->email;
             $emails[] = $email;
            }
         }
         return $emails;
      }
   }
}