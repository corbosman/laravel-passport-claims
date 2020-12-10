<?php

namespace CorBosman\Passport;

use DateTimeImmutable;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Configuration;
use Illuminate\Pipeline\Pipeline;
use League\OAuth2\Server\CryptKey;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use CorBosman\Passport\Traits\ClaimTrait;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Laravel\Passport\Bridge\AccessToken as PassportAccessToken;

class AccessToken extends PassportAccessToken
{
    use ClaimTrait;

    /**
     * @var CryptKey
     */
    private $privateKey;

    /**
     * @var Configuration
     */
    private $jwtConfiguration;


    /**
     * Generate a string representation from the access token
     */
    public function __toString()
    {
        return $this->convertToJWT()->toString();
    }

    /**
     * Set the private key used to encrypt this access token.
     * @param CryptKey $privateKey
     */
    public function setPrivateKey(CryptKey $privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * Initialise the JWT Configuration.
     */
    public function initJwtConfiguration()
    {
        $this->jwtConfiguration = Configuration::forAsymmetricSigner(
            new Sha256(),
            LocalFileReference::file($this->privateKey->getKeyPath(), $this->privateKey->getPassPhrase() ?? ''),
            InMemory::plainText('')
        );
    }

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
