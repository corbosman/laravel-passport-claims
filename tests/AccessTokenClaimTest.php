<?php

namespace CorBosman\Passport\Tests;

use Mockery as m;
use Lcobucci\JWT\Parser;
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

class AccessTokenClaimTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_can_add_claims_to_token()
    {
        /* set up the environment */
        $repository = new AccessTokenRepository(m::mock(TokenRepository::class), m::mock(Dispatcher::class));
        $client = new Client('client-id', 'name', 'redirect');
        $scopes = [];
        $userIdentifier = 1;
        $keys = (new RSA())->createKey(1024);

        /* set custom claims, defined below this test */
        app('config')->set('passport-claims.claims', [MyClaim::class, AnotherClaim::class]);

        /* create the laravel token */
        $token = $repository->getNewToken($client, $scopes, $userIdentifier);
        $token->setPrivateKey(new CryptKey($keys['privatekey']));
        $token->setExpiryDateTime(CarbonImmutable::now()->addHour());

        /* convert the token to a JWT and parse the JWT back to a Token */
        $jwt = (new Parser())->parse($token->__toString());

        /* assert our claims were set on the token */
        $this->assertEquals('test', $jwt->getClaim('my-claim'));
        $this->assertEquals('test', $jwt->getClaim('another-claim'));
    }
}

