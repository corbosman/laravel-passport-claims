<?php

namespace CorBosman\Passport\Tests\Claims;

use Laravel\Passport\Bridge\AccessToken;

class MyClaim
{
    public function handle(AccessToken $token, $next)
    {
        $token->addClaim('my-claim', 'test');

        return $next($token);
    }
}
