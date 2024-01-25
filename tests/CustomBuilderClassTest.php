<?php

namespace CorBosman\Passport\Tests;

use CorBosman\Passport\ServiceProvider;
use CorBosman\Passport\Tests\Builders\CustomAccessTokenBuilderAddsIssuer;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Mockery as m;
use Lcobucci\JWT\Token\Parser;
use phpseclib\Crypt\RSA;
use Carbon\CarbonImmutable;
use Orchestra\Testbench\TestCase;
use League\OAuth2\Server\CryptKey;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\TokenRepository;
use Illuminate\Contracts\Events\Dispatcher;
use CorBosman\Passport\Tests\Claims\MyClaim;
use CorBosman\Passport\AccessTokenRepository;
use CorBosman\Passport\Tests\Claims\AnotherClaim;

class CustomBuilderClassTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_can_add_custom_builder()
    {
        /* set custom token builder */
        app('config')->set('passport-claims.builder', CustomAccessTokenBuilderAddsIssuer::class);

        /* set up the environment */
        $repository = new AccessTokenRepository(m::mock(TokenRepository::class), m::mock(Dispatcher::class));
        $client = new Client('client-id', 'name', 'redirect');
        $scopes = [];
        $userIdentifier = 1;
        $keys = (new RSA())->createKey(2048);

        /* create the laravel token */
        $token = $repository->getNewToken($client, $scopes, $userIdentifier);
        $token->setPrivateKey(new CryptKey($keys['privatekey']));
        $token->setExpiryDateTime(CarbonImmutable::now()->addHour());
        $token->setIdentifier('test');

        /* convert the token to a JWT and parse the JWT back to a Token */
        $jwt = (new Parser(new JoseEncoder))->parse($token->__toString());

        /* assert our claims were set on the token */
        $this->assertEquals(true, $jwt->hasBeenIssuedBy('CustomTokenIssuer'));
    }
}

