<?php

namespace CorBosman\Passport;

use Laravel\Passport\Passport;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;

class AccessTokenRepository extends PassportAccessTokenRepository
{
    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        \Laravel\Passport\Passport::useAccessTokenEntity(config('passport-claims.builder', AccessToken::class));
        return new \Laravel\Passport\Passport::$accessTokenEntity($userIdentifier, $scopes, $clientEntity);
    }
}
