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
use API\Model\AccessToken;
use API\Model\Scope;
use API\Model\App;

class SessionStorage extends AbstractStorage implements SessionInterface
{
   public function getByAccessToken(AccessTokenEntity $accessToken) {
      $accessToken = AccessToken::where('token', '=', $accessToken->getId())->first();
      if ($accessToken) {
         $_session = $accessToken->session;
         $session = new SessionEntity($this->server);
         $session->setId($_session->id);
         $session->setOwner($_session->owner_type, $_session->owner_id);

         return $session;
      }
   }

   public function getByAuthCode(AuthCodeEntity $authCode) {

   }

   public function getScopes(SessionEntity $session) {
      $session = Session::where('id', '=', $session->getId())->first();

      $scopes = [];

      if ($session) {
         $_scopes = $session->scopes()->get();
         foreach ($_scopes as $scope) {
            $scopes[] = (new ScopeEntity($this->server))->hydrate([
               "id"             =>    $scope['identifier'],
               "description"    =>    $scope['description']
            ]);
         }
      }

      return $scopes;
   }

   public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null) {
      $app = App::where('id', '=', $clientId)->first();
      if ($app) {
         $session = new Session();
         $session->owner_type = $ownerType;
         $session->app_id = $app->id;

         if ($ownerType == 'client') {
            $session->owner_id = null;
         } else { // ($ownerType == 'user') assumed anyway
            $session->owner_id = $ownerId;
         }

         $session->save();
         return $session->id;
      }
   }

   public function associateScope(SessionEntity $session, ScopeEntity $scope) {
      $session = Session::where('id', '=', $session->getId())->first();
      $scope   = Scope::where('identifier', '=', $scope->getId())->first();

      if ($session && $scope) {
         $session->scopes()->attach($scope);
      }
   }
}