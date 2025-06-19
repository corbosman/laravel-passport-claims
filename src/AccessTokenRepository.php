<?php

namespace CorBosman\Passport;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;

class AccessTokenRepository extends PassportAccessTokenRepository
{
    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, ?string $userIdentifier = null): AccessTokenEntityInterface
    {
        return new AccessToken($userIdentifier, $scopes, $clientEntity);
    }
}
