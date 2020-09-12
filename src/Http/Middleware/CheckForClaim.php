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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $claim)
    {
        if ( ! ($token = $request->bearerToken())) {
            throw new AuthenticationException;
        }

        try {
            $jwt = (new Parser())->parse($token);
        } catch(\Exception $e) {
            throw new AuthenticationException;
        }

        if ($jwt->hasClaim($claim)) {
            return $next($request);
        }

        throw new AuthenticationException('Unauthenticated: missing claim');

    }
}
