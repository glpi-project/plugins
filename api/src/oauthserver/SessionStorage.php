<?php

namespace API\OAuthServer;

use Illuminate\Database\Capsule\Manager as Capsule;
use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\SessionInterface;

use API\Model\Session;
use API\Model\App;

class SessionStorage extends AbstractStorage implements SessionInterface
{
   public function getByAccessToken(AccessTokenEntity $accessToken) {
      $_session = Session::join('access_tokens', 'access_token.session_id', '=', 'session.id')
                        ->where('access_token.token', '=', $accessToken)
                        ->first();

      if ($_session) {
         $session = new SessionEntity($this->server);
         $session->setId($_session->id);
         $session->setOwner($_session->owner_type, $_session->owner_id);

         return $session;
      }
   }

   public function getByAuthCode(AuthCodeEntity $authCode) {

   }

   public function getScopes(SessionEntity $session) {

   }

   public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null) {
      $app = App::where('id', '=', $clientId)->first();
      if ($app) {
         $session = new Session();
         $session->owner_type = $ownerType;
         $session->owner_id = $ownerId;
         $session->app_id = $app->id;
         $session->save();
         return $session->id;
      }
   }

   public function associateScope(SessionEntity $session, ScopeEntity $scope) {

   }
}