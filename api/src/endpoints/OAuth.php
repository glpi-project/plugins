<?php

use API\Core\Tool;
use API\Core\OAuthClient;
use \API\Model\User;
use \API\Model\UserExternalAccount;
use \API\Model\AccessToken;
use \API\Exception\NoCredentialsLeft;
use \API\OAuthServer\AuthorizationServer;
use \API\OAuthServer\OAuthHelper;

/**
 * Callback from a OAuth2 supported external service which handles
 *  - creation of a GLPi Plugins account from an external service account
 *  - association of an external service account to an existing GLPi Plugins account
 *  - authentification is external account is already known to be linked to
 *    a GLPi Plugins account
 */
$user_associate_external_account = Tool::makeEndpoint(function($service) use($app, $resourceServer) {
   $oAuth = new OAuthClient($service);
   $token = $oAuth->getAccessToken($app->request->get('code'));
   $data = [];


   if (isset($_COOKIE['access_token'])) {
      $alreadyAuthed = true;

      // this is OUR access token, not the provider's one
      $accessToken = AccessToken::where('token', '=', $_COOKIE['access_token'])->first();
      setcookie('access_token', '', 1, '/');
      if (!$accessToken) {
         Tool::endWithJson([
            "error" => "You provided a wrong access_token via cookie"
         ]);
      } else {
         $user_id = $accessToken->session->user->id;
      }
   } else {
      $alreadyAuthed = false;
   }

   $external_account_infos = $oAuth->getInfos($token);

   if ($alreadyAuthed) {
      $user = User::where('id', '=', $user_id)->first();
      if (!$user) {
         Tool::log('warning: session has unexisting user_id '.$user_id);
         Tool::endWithJson([
            "error" => "Service error"
         ], 400);
      }

      $externalAccount = $user->externalAccounts()
               ->where('external_user_id', '=', $external_account_infos['id'])
               ->where('service', '=', $service)
               ->first();

      if (!$externalAccount) {
         if ($_externalAccount = UserExternalAccount::where('external_user_id', '=', $external_account_infos['id'])->first()) {
            $data['error'] = 'EXTERNAL_ACCOUNT_ALREADY_PAIRED';
         } else {
            $externalAccount = new UserExternalAccount;
            $externalAccount->external_user_id = $external_account_infos['id'];
            $externalAccount->token = $token;
            $externalAccount->service = $service;
            $user->externalAccounts()->save($externalAccount);

            $data['external_account_linked'] = true;
         }
      } else {
         $data['error'] = 'You are already authed, and that '.$service.' account is already linked';
      }
   } else {
      $externalAccount = UserExternalAccount::where('external_user_id', '=', $external_account_infos['id'])->first();

      // If we know that external account
      if ($externalAccount) {
         $user = $externalAccount->user;

         $accessToken = OAuthHelper::createAccessTokenFromUserId(
            $user->id,
            ['plugins', 'plugins:search', 'plugin:card', 'plugin:star',
             'plugin:submit', 'plugin:download', 'tags', 'tag', 'authors',
             'author', 'version', 'message', 'user', 'user:externalaccounts',
             'user:apps']
         );
      }
      else { // Else we're creating a new
         // local account, and are associating the external one
         // to that new local one
         $user = new User;
         $user->active = 0; // Notice it is created as non active
         $user->username = $external_account_infos['username'];
         if (isset($external_account_infos['realname'])) {
            $user->realname = $external_account_infos['realname'];
         }
         if (isset($external_account_infos['location'])) {
            $user->location = $external_account_infos['location'];
         }
         if (isset($external_account_infos['website'])) {
            $user->website = $external_account_infos['website'];
         }
         $user->save();
         $data['account_created'] = true;

         // Associating external user account
         $externalAccount = new UserExternalAccount;
         $externalAccount->external_user_id = $external_account_infos['id'];
         $externalAccount->token = $token;
         $externalAccount->service = $service;
         $user->externalAccounts()->save($externalAccount);
         $data['external_account_linked'] = true;

         $accessToken = OAuthHelper::createAccessTokenFromUserId(
            $user->id,
            ['user']
         );
      }
      $data['access_token'] = $accessToken['token'];
      $data['refresh_token'] = $accessToken['refresh_token'];
      $data['access_token_expires_in'] = $accessToken['ttl'];
   }

   if (isset($data['error'])) {
      if ($data['error'] == 'Service error') {
         $app->response->status(500);
      } else {
         $app->response->status(400);
      }
   }

   echo '<!DOCTYPE html><html><head></head><body><script type="text/javascript">'.
           'var data = \''.json_encode($data).'\'; var i = 0 ; var interval = setInterval(function(){  if (i == 300) {clearInterval(interval);} i++; window.postMessage(data, "*");}, 750);'.
        '</script></body></html>';
});

/**
 * Authorize an user, providing him an
 * access token, this is directly backed
 * by leaguephp/oauth2-server
 */
$authorize = Tool::makeEndpoint(function() use($app) {
   $authorizationServer = new AuthorizationServer();
   Tool::endWithJson($authorizationServer->issueAccessToken(), 200);
});

/**
 * Return the complete list of external accounts for an
 * authentified user
 */
$user_external_accounts = Tool::makeEndpoint(function() use ($app, $resourceServer) {
   OAuthHelper::needsScopes(['user:externalaccounts']);

   $user = OAuthHelper::currentlyAuthed();

   $external_accounts = $user->externalAccounts()->get();
   Tool::endWithJson($external_accounts, 200);
});

/**
 * Delete the link between GLPi Plugins account and
 * remote OAuth2 account. There is a security that
 * avoir the user to remove his last external account
 * if it is the only way for him to auth back to the
 * system.
 *
 * @note: if the account is activated, the
 * user could have used the "I forgot my password"
 * feature, but we still prefer to alert the user that
 * he need to setup basic password.
 */
$user_delete_external_account = Tool::makeEndpoint(function($id) use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user:externalaccounts']);

   $user = OAuthHelper::currentlyAuthed();

   $externalAccount = $user->externalAccounts()->find($id);
   if ($externalAccount) {
      $user_accounts_count = $user->externalAccounts()->count();
      // if there is only one external
      // account left, it means we expect
      // the user to at least have a password
      // so he can log in back to the system
      if ($user_accounts_count == 1 &&
          $user->password == NULL) {
         // if he doesn't we won't let him
         // delete the external account link
         throw new NoCredentialsLeft;
      }

      $externalAccount->delete();
   }


   $app->halt(200);
});

/**
 * Returns the complete list of emails
 * that are available through the
 * external accounts
 */
$oauth_external_emails = Tool::makeEndpoint(function() use($app, $resourceServer) {
   OAuthHelper::needsScopes(['user']);

   $user = OAuthHelper::currentlyAuthed();

   $externalAccounts = $user->externalAccounts()->get();

   $emails = [];
   foreach ($externalAccounts as $externalAccount) {
      $oAuth = new OAuthClient($externalAccount->service);
      $_emails = $oAuth->getEmails($externalAccount->token);
      foreach ($_emails as $email) {
         $emails[] = ["email" => $email,
                      "service" => $externalAccount->service];
      }
   }

   Tool::endWithJson($emails);
});

$app->get('/user/external_accounts', $user_external_accounts);
$app->delete('/user/external_accounts/:id', $user_delete_external_account);
$app->get('/oauth/available_emails', $oauth_external_emails);
$app->get('/oauth/associate/:service', $user_associate_external_account);
$app->post('/oauth/authorize', $authorize);
$app->options('/user/external_accounts', function() {});
$app->options('/oauth/available_emails', function() {});
$app->options('/oauth/authorize', function() {});
$app->options('/oauth/associate/:service', function() {});