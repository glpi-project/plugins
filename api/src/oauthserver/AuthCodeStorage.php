<?php

namespace API\OAuthServer;

use Illuminate\Database\Capsule\Manager as Capsule;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AuthCodeInterface;

class AuthCodeStorage extends AbstractStorage implements AuthCodeInterface
{
   public function get($code) {

   }

   public function create($token, $expireTime, $sessionId, $redirectUri) {

   }

   public function getScopes(AuthCodeEntity $token)
   {

   }

   public function associateScope(AuthCodeEntity $token, ScopeEntity $scope) {

   }

   public function delete(AuthCodeEntity $token) {

   }
}