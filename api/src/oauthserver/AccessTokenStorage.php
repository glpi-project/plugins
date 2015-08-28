<?php

namespace API\OAuthServer;

use Illuminate\Database\Capsule\Manager as Capsule;
use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AccessTokenInterface;

use \API\Model\AccessToken;
use \API\Model\Scope;

class AccessTokenStorage extends AbstractStorage implements AccessTokenInterface
{
   public function get($token) {
      $token = AccessToken::where('token', '=', $token);

      if ($token-->count() != 1) {
         return;
      } else {
         $token = $token->first();
      }

      $token = (new AccessTokenEntity($this->server))
                  ->setId($token->token)
                  ->setExpireTime($token->expire_time);
      return $token;
   }

   public function getScopes(AccessTokenEntity $token) {
      $scopes = Scope::select(['scopes.identifier', 'scopes.description'])
                     ->join('sessions_scopes', 'sessions_scopes.scope_id', '=', 'scopes.id')
                     ->join('sessions', 'sessions.id', '=', 'sessions_scopes.session_id')
                     ->join('access_tokens', 'access_tokens.session_id', '=', 'sessions.id')
                     ->where('access_tokens.token', '=', $token->getId())
                     ->get();

      $response = [];

      foreach($scopes as $scope) {
         $scope = (new ScopeEntity($this->server))->hydrate([
            'id' => $scope->identifier,
            'description' => $scope->description
         ]);
         $response[] = $scope;
      }

      return $response;
   }

   public function create($token, $expireTime, $sessionId) {

   }

   public function associateScope(AccessTokenEntity $token, ScopeEntity $scope) {

   }

   public function delete(AccessTokenEntity $token)
   {

   }
}