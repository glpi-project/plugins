<?php

namespace API\OAuthServer;

use Illuminate\Database\Capsule\Manager as Capsule;
use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ClientInterface;

use API\Model\App;

class ClientStorage extends AbstractStorage implements ClientInterface
{
   public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null) {
      $query = App::where('id', '=', $clientId);

      if ($clientSecret !== null) {
         $query->where('secret', '=', $clientSecret);
      }

      if ($redirectUri) {
         $query->where('redirect_uri', '=', $redirectUri);
      }

      $result = $query->get();

      if (count($result) === 1) {
         $client = new ClientEntity($this->server);
         $client->hydrate([
            'id'   => $result[0]['id'],
            'name' => $result[0]['name']
         ]);
         return $client;
      }

      return;
   }

   public function getBySession(SessionEntity $session) {

   }
}