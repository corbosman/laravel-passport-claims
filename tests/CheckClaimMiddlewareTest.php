<?php

namespace CorBosman\Passport\Tests;

use Mockery as m;
use phpseclib\Crypt\RSA;
use Carbon\CarbonImmutable;
use Orchestra\Testbench\TestCase;
use League\OAuth2\Server\CryptKey;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\TokenRepository;
use Illuminate\Contracts\Events\Dispatcher;
use CorBosman\Passport\Tests\Claims\MyClaim;
use Illuminate\Auth\AuthenticationException;
use CorBosman\Passport\AccessTokenRepository;
use CorBosman\Passport\Http\Middleware\CheckForClaim;

class CheckClaimMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        CheckForClaim::setToken();
        m::close();
    }

    public function test_request_is_passed_along_if_claim_is_present_on_token()
    {
        /* set up token with custom claim */
        $repository = new AccessTokenRepository(m::mock(TokenRepository::class), m::mock(Dispatcher::class));
        $client = new Client('client-id', 'name', 'redirect');
        $keys = (new RSA())->createKey(2048);
        app('config')->set('passport-claims.claims', [MyClaim::class]);
        $token = $repository->getNewToken($client, [], '');
        $token->setPrivateKey(new CryptKey($keys['privatekey']));
        $token->setExpiryDateTime(CarbonImmutable::now()->addHour());
        $token->setIdentifier('test');

        /* set up request */
        $request = m::mock();
        $request->shouldReceive('bearerToken')->andReturn($token->__toString());

        $middleware = new CheckForClaim;

        $response = $middleware->handle($request, function () {
            return 'response';
        }, 'my-claim');

        $this->assertSame('response', $response);
    }

    public function test_request_is_passed_along_if_claim_matches_a_value()
    {
        /* set up token with custom claim */
        $repository = new AccessTokenRepository(m::mock(TokenRepository::class), m::mock(Dispatcher::class));
        $client = new Client('client-id', 'name', 'redirect');
        $keys = (new RSA())->createKey(2048);
        app('config')->set('passport-claims.claims', [MyClaim::class]);
        $token = $repository->getNewToken($client, [], '');
        $token->setPrivateKey(new CryptKey($keys['privatekey']));
        $token->setExpiryDateTime(CarbonImmutable::now()->addHour());
        $token->setIdentifier('test');

        /* set up request */
        $request = m::mock();
        $request->shouldReceive('bearerToken')->andReturn($token->__toString());

        $middleware = new CheckForClaim;

        $response = $middleware->handle($request, function () {
            return 'response';
        }, 'my-claim', 'test');

        $this->assertSame('response', $response);
    }

    public function test_request_is_passed_along_if_claim_matches_a_value_from_many()
    {
        /* set up token with custom claim */
        $repository = new AccessTokenRepository(m::mock(TokenRepository::class), m::mock(Dispatcher::class));
        $client = new Client('client-id', 'name', 'redirect');
        $keys = (new RSA())->createKey(2048);
        app('config')->set('passport-claims.claims', [MyClaim::class]);
        $token = $repository->getNewToken($client, [], '');
        $token->setPrivateKey(new CryptKey($keys['privatekey']));
        $token->setExpiryDateTime(CarbonImmutable::now()->addHour());
        $token->setIdentifier('test');

        /* set up request */
        $request = m::mock();
        $request->shouldReceive('bearerToken')->andReturn($token->__toString());

        $middleware = new CheckForClaim;

        $response = $middleware->handle($request, function () {
            return 'response';
        }, 'my-claim', 'test|test2');

        $this->assertSame('response', $response);
    }

    public function test_exception_is_thrown_if_token_doesnt_have_claim()
    {
        $this->expectException(AuthenticationException::class);

        /* set up token without any custom claims */
        $repository = new AccessTokenRepository(m::mock(TokenRepository::class), m::mock(Dispatcher::class));
        $client = new Client('client-id', 'name', 'redirect');
        $keys = (new RSA())->createKey(2048);
        $token = $repository->getNewToken($client, [], '');
        $token->setPrivateKey(new CryptKey($keys['privatekey']));
        $token->setExpiryDateTime(CarbonImmutable::now()->addHour());
        $token->setIdentifier('test');

        /* set up request */
        $request = m::mock();
        $request->shouldReceive('bearerToken')->andReturn($token->__toString());

        $middleware = new CheckForClaim;

        $response = $middleware->handle($request, function () {
            return 'response';
        }, 'my-claim');
    }

    public function test_exception_is_thrown_if_claim_does_not_match_value()
    {
        $this->expectException(AuthenticationException::class);

        /* set up token with custom claim */
        $repository = new AccessTokenRepository(m::mock(TokenRepository::class), m::mock(Dispatcher::class));
        $client = new Client('client-id', 'name', 'redirect');
        $keys = (new RSA())->createKey(2048);
        app('config')->set('passport-claims.claims', [MyClaim::class]);
        $token = $repository->getNewToken($client, [], '');
        $token->setPrivateKey(new CryptKey($keys['privatekey']));
        $token->setExpiryDateTime(CarbonImmutable::now()->addHour());
        $token->setIdentifier('test');

        /* set up request */
        $request = m::mock();
        $request->shouldReceive('bearerToken')->andReturn($token->__toString());

        $middleware = new CheckForClaim;

        $response = $middleware->handle($request, function () {
            return 'response';
        }, 'my-claim', 'foo');
    }

    public function test_exception_is_thrown_if_request_doesnt_have_token()
    {
        $this->expectException(AuthenticationException::class);

        /* set up request */
        $request = m::mock();
        $request->shouldReceive('bearerToken')->andReturn(false);

        $middleware = new CheckForClaim;

        $response = $middleware->handle($request, function () {
            return 'response';
        }, 'my-claim');

        $this->assertSame('response', $response);
    }

    public function test_exception_is_thrown_if_token_doesnt_parse()
    {
        $this->expectException(AuthenticationException::class);

        /* set up request */
        $request = m::mock();
        $request->shouldReceive('bearerToken')->andReturn('invalidtoken');

        $middleware = new CheckForClaim;

        $response = $middleware->handle($request, function () {
            return 'response';
        }, 'my-claim');

        $this->assertSame('response', $response);
    }

}
