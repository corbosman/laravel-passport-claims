<?php

namespace CorBosman\Passport\Tests\Claims;

use Laravel\Passport\Bridge\AccessToken;

class AnotherClaim
{
    public function handle(AccessToken $token, $next)
    {
        $token->addClaim('another-claim', 'test');

        return $next($token);
    }
}
