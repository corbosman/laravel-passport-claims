<?php

namespace CorBosman\Passport;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Illuminate\Pipeline\Pipeline;
use League\OAuth2\Server\CryptKey;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use CorBosman\Passport\Traits\ClaimTrait;
use Laravel\Passport\Bridge\AccessToken as PassportAccessToken;

class AccessToken extends PassportAccessToken
{
    use ClaimTrait;

    private $privateKey;

    /**
     * Generate a string representation from the access token
     */
    public function __toString()
    {
        return (string) $this->convertToJWT($this->privateKey);
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
     * override the JWT so we can add our own claims
     *
     * @param CryptKey $privateKey
     * @return \Lcobucci\JWT\Token
     */
    public function convertToJWT(CryptKey $privateKey)
    {
        $jwt = (new Builder())
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier(), true)
            ->issuedAt(time())
            ->canOnlyBeUsedAfter(time())
            ->expiresAt($this->getExpiryDateTime()->getTimestamp())
            ->relatedTo($this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes());

        collect(app(Pipeline::class)
            ->send($this)
            ->through(config('passport-claims.claims', []))
            ->thenReturn()
            ->claims())
            ->each(function($value, $key) use ($jwt) {
                $jwt->withClaim($key, $value);
            });

        return $jwt->getToken(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()));
    }
}
