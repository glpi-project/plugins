<?php

namespace API\OAuthServer;

use Illuminate\Database\Capsule\Manager as Capsule;
use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AccessTokenInterface;

use \API\OAuthServer\OAuthHelper;

use \API\Model\AccessToken;
use \API\Model\Scope;

class AccessTokenStorage extends AbstractStorage implements AccessTokenInterface
{
   public function get($token) {
      $token = AccessToken::where('token', '=', $token);

      if ($token->count() != 1) {
         return;
      } else {
         $token = $token->first();
      }

      $expireTime = (new \DateTime($token->expire_time))->getTimestamp();

      $token = (new AccessTokenEntity($this->server))
                  ->setId($token->token)
                  ->setExpireTime($expireTime);
      return $token;
   }

   public function getScopes(AccessTokenEntity $token) {
      $token = AccessToken::where('token', '=', $token->getId())->first();

      $scopes = [];

      if ($token) {
         $_scopes = $token->scopes()->get();
         foreach ($_scopes as $scope) {
            $scopes[] = (new ScopeEntity($this->server))->hydrate([
               "id"             =>    $scope['identifier'],
               "description"    =>    $scope['description']
            ]);
         }
      }

      return $scopes;
   }

   public function create($token, $expireTime, $sessionId) {
      $accessToken = new AccessToken();
      $accessToken->token = $token;
      $accessToken->session_id = $sessionId;
      $accessToken->expire_time = Capsule::raw('FROM_UNIXTIME('.$expireTime.')');
      $accessToken->save();
   }

   public function associateScope(AccessTokenEntity $token, ScopeEntity $scope) {
      $token = AccessToken::where('token', '=', $token->getId())->first();
      $scope = Scope::where('identifier', '=', $scope->getId())->first();

      if ($token && $scope) {
         $token->scopes()->attach($scope);
      }
   }

   public function delete(AccessTokenEntity $token)
   {
      Scope::where('id', '=', $token->getId())->delete();
   }
}