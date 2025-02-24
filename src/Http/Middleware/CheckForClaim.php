<?php

namespace CorBosman\Passport\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;

class CheckForClaim
{
    /** @var \Lcobucci\JWT\Token|null */
    private static $jwt;

    public static function setToken(?Token $jwt = null): void
    {
        static::$jwt = $jwt;
    }

    /**
     * @param \Illuminate\Http\Request $request
     */
    public static function getToken($request = null)
    {
        if (static::$jwt) {
            return static::$jwt;
        }

        /* check for presence of token */
        if ( ! ($token = ($request ?: request())?->bearerToken())) {
            return null;
        }

        /* check if token parses properly */
        try {
            static::$jwt = ((new Parser(new JoseEncoder))->parse($token));
        } catch(\Exception $e) {
            return null;
        }

        return static::$jwt;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $claim
     * @return mixed
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next, $claim, $values = null)
    {
        $jwt = static::getToken($request);

        if ( ! $jwt) {
            throw new AuthenticationException;
        }

        /* check if we want to check both claim and value */
        if ($jwt->claims()->has($claim)) {

            if ($values === null) {
                return $next($request);
            }

            $claimValue = $jwt->claims()->get($claim);

            foreach (explode('|', (string) $values) as $value) {
                if ($claimValue == $value) {
                    return $next($request);
                }
            }

        }

        throw new AuthenticationException('Unauthenticated: missing claim');

    }
}
