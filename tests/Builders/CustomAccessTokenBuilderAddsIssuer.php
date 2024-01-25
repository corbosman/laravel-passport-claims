<?php

namespace CorBosman\Passport\Tests\Builders;

use CorBosman\Passport\ClaimsFormatter;
use CorBosman\Passport\Traits\ClaimTrait;
use Illuminate\Pipeline\Pipeline;
use Laravel\Passport\Bridge\AccessToken as PassportAccessToken;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use \DateTimeImmutable;

class CustomAccessTokenBuilderAddsIssuer extends PassportAccessToken implements AccessTokenEntityInterface
{
    use AccessTokenTrait, ClaimTrait;

    /**
     * Generate a JWT from the access token
     *
     * @return Token
     */
    private function convertToJWT() : Token
    {
        $this->initJwtConfiguration();

        $jwt = $this->jwtConfiguration->builder(ClaimsFormatter::formatters())
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo((string) $this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes())
            ->issuedBy('CustomTokenIssuer');

        return collect(app(Pipeline::class)
            ->send($this)
            ->through(config('passport-claims.claims', []))
            ->thenReturn()
            ->claims())
            ->reduce(fn($jwt, $value, $key) => $jwt->withClaim($key, $value), $jwt)
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }
}