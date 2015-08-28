<?php

namespace API\OAuthServer;

use Illuminate\Database\Capsule\Manager as Capsule;
use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\SessionInterface;

class SessionStorage extends AbstractStorage implements SessionInterface
{
   public function getByAccessToken(AccessTokenEntity $accessToken) {

   }

   public function getByAuthCode(AuthCodeEntity $authCode) {

   }

   public function getScopes(SessionEntity $session) {

   }

   public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null) {

   }

   public function associateScope(SessionEntity $session, ScopeEntity $scope) {
      
   }
}