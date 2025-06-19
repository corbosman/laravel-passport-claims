<?php

namespace CorBosman\Passport;

use DateTimeImmutable;
use Illuminate\Pipeline\Pipeline;
use Lcobucci\JWT\Token;
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
            ->withClaim('scopes', $this->getScopes());

        if (config('passport-claims.issuer')) {
            $jwt = $jwt->issuedBy(config('passport-claims.issuer'));
        }

        return collect(app(Pipeline::class)
            ->send($this)
            ->through(config('passport-claims.claims', []))
            ->thenReturn()
            ->claims())
            ->reduce(fn($jwt, $value, $key) => $jwt->withClaim($key, $value), $jwt)
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }

    public function __toString()
    {
        return $this->convertToJWT()->toString();
    }
}
