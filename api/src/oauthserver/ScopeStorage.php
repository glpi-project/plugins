<?php

namespace API\OAuthServer;

use Illuminate\Database\Capsule\Manager as Capsule;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ScopeInterface;

use \API\Model\Scope;

class ScopeStorage extends AbstractStorage implements ScopeInterface
{
   public function get($scope, $grantType = null, $clientId = null) {
      $scope = Scope::where('identifier', '=', $scope)->first();

      if (!$scope) {
         return;
      }

      return (new ScopeEntity($this->server))->hydrate([
         "id" => $scope->identifier,
         "description" => $scope->description
      ]);
   }
}