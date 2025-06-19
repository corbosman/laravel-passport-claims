<?php

namespace CorBosman\Passport\Tests;

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

class AccessTokenClaimTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        restore_error_handler();
        restore_exception_handler();
    }

    public function test_can_add_claims_to_token()
    {
        /* set up the environment */
        $repository = new AccessTokenRepository(m::mock(Dispatcher::class));
        $client = new Client('client-id', 'name', ['redirect']);
        $scopes = [];
        $userIdentifier = 1;
        $keys = (new RSA())->createKey(2048);

        /* set custom claims, defined below this test */
        app('config')->set('passport-claims.claims', [MyClaim::class, AnotherClaim::class]);

        /* create the laravel token */
        $token = $repository->getNewToken($client, $scopes, $userIdentifier);
        $token->setPrivateKey(new CryptKey($keys['privatekey']));
        $token->setExpiryDateTime(CarbonImmutable::now()->addHour());
        $token->setIdentifier('test');

        /* convert the token to a JWT and parse the JWT back to a Token */
        $jwt = (new Parser(new JoseEncoder))->parse($token->__toString());

        /* assert our claims were set on the token */
        $this->assertEquals('test', $jwt->claims()->get('my-claim'));
        $this->assertEquals('test', $jwt->claims()->get('another-claim'));
    }

    public function test_jwt_dose_not_include_iss_claim_by_default()
    {
         /* set up the environment */
         $repository = new AccessTokenRepository(m::mock(Dispatcher::class));
         $client = new Client('client-id', 'name', ['redirect']);
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
        $this->assertEquals(null, $jwt->claims()->get('iss'));
    }

    public function test_jwt_has_iss_claim_when_configured()
    {
         /* set up the environment */
         $repository = new AccessTokenRepository(m::mock(Dispatcher::class));
         $client = new Client('client-id', 'name', ['redirect']);
         $scopes = [];
         $userIdentifier = 1;
         $keys = (new RSA())->createKey(2048);

        /* set custom claims, defined below this test */
        app('config')->set('passport-claims.issuer', 'https://example.com');

        /* create the laravel token */
        $token = $repository->getNewToken($client, $scopes, $userIdentifier);
        $token->setPrivateKey(new CryptKey($keys['privatekey']));
        $token->setExpiryDateTime(CarbonImmutable::now()->addHour());
        $token->setIdentifier('test');

        /* convert the token to a JWT and parse the JWT back to a Token */
        $jwt = (new Parser(new JoseEncoder))->parse($token->__toString());

        /* assert our claims were set on the token */
        $this->assertEquals('https://example.com', $jwt->claims()->get('iss'));
    }
}

