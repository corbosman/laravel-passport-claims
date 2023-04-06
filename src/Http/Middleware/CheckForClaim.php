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
    public function handle($request, Closure $next, $claim, $value = null)
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

            if ($value === null) {
                return $next($request);
            }

            if ($jwt->claims()->get($claim) === $value) {
                return $next($request);
            }

        }

        throw new AuthenticationException('Unauthenticated: missing claim');

    }
}
