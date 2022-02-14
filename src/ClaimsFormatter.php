<?php

namespace CorBosman\Passport;

use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\MicrosecondBasedDateConversion;
use Lcobucci\JWT\Encoding\UnifyAudience;

class ClaimsFormatter
{
    public static function formatters(): ChainedFormatter
    {
        $formatters = array_map(function ($formatter) {
            return new $formatter;
        }, config('passport-claims.formatters', []));

        return count($formatters) > 0 ? new ChainedFormatter(...$formatters) : ChainedFormatter::default();
    }
}
