<?php

namespace CorBosman\Passport;

use DateTimeImmutable;
use Illuminate\Pipeline\Pipeline;
use CorBosman\Passport\Traits\ClaimTrait;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use Laravel\Passport\Bridge\AccessToken as PassportAccessToken;

class AccessToken extends PassportAccessToken
{
    use AccessTokenTrait, ClaimTrait;

    /**
     * Generate a JWT from the access token
     *
     * @return Token
     */
    private function convertToJWT()
    {
        $this->initJwtConfiguration();

        $jwt = $this->jwtConfiguration->builder()
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo((string) $this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes());

        collect(app(Pipeline::class)
            ->send($this)
            ->through(config('passport-claims.claims', []))
            ->thenReturn()
            ->claims())
            ->each(function($value, $key) use ($jwt) {
                $jwt->withClaim($key, $value);
            });

        return $jwt->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }
}
