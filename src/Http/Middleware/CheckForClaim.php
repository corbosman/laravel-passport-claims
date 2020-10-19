<?php

namespace CorBosman\Passport\Http\Middleware;

use Closure;
use Lcobucci\JWT\Parser;
use Illuminate\Auth\AuthenticationException;

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
    public function handle($request, Closure $next, $claim)
    {
        /* check for presence of token */
        if ( ! ($token = $request->bearerToken())) {
            throw new AuthenticationException;
        }

        /* check if token parses properly */
        try {
            $jwt = (new Parser())->parse($token);
        } catch(\Exception $e) {
            throw new AuthenticationException;
        }

        /* check if we want to check both claim and value */
        [$claim, $value] = explode(',', $claim . ',');

        if ($jwt->hasClaim($claim)) {

            if (!$value) {
                return $next($request);
            }

            if ($jwt->getClaim($claim) === $value) {
                return $next($request);
            }

        }

        throw new AuthenticationException('Unauthenticated: missing claim');

    }
}
