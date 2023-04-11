<?php

namespace CorBosman\Passport\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;

class CheckForClaim
{
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
        /* check for presence of token */
        if ( ! ($token = $request->bearerToken())) {
            throw new AuthenticationException;
        }

        /* check if token parses properly */
        try {
            $jwt = ((new Parser(new JoseEncoder))->parse($token));
        } catch(\Exception $e) {
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
