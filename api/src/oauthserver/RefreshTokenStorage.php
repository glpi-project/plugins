<?php

namespace API\OAuthServer;

use Illuminate\Database\Capsule\Manager as DB;
use League\OAuth2\Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\RefreshTokenInterface;

use API\Model\RefreshToken;
use API\Model\AccessToken;

class RefreshTokenStorage extends AbstractStorage implements RefreshTokenInterface
{
    public function get($token)
    {
        $refreshToken = RefreshToken::where('token', '=', $token)->first();

        if ($refreshToken) {
            $accessToken = $refreshToken->accessToken;
            $expireTime = (new \DateTime($refreshToken->expire_time))->getTimestamp();
            $token = (new RefreshTokenEntity($this->server))
                         ->setId($refreshToken->token)
                         ->setExpireTime($expireTime)
                         ->setAccessTokenId($accessToken->token);
            return $token;
        }

        return;
    }

    public function create($token, $expireTime, $accessToken)
    {
        $accessToken = AccessToken::where('token', '=', $accessToken)->first();

        $refreshToken = new RefreshToken;
        $refreshToken->token = $token;
        $refreshToken->access_token_id = $accessToken->id;
        $refreshToken->expire_time = DB::raw('FROM_UNIXTIME('.$expireTime.')');

        $refreshToken->save();
    }

    public function delete(RefreshTokenEntity $token)
    {
        $refreshToken = RefreshToken::where('token', '=', $token)
                                    ->delete();
    }
}
