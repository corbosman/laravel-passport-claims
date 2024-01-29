<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Claim Classes
    |--------------------------------------------------------------------------
    |
    | Here you can add an array of classes that will be each be called to add
    | claims to the passport JWT token. See the readme for the interface that
    | these classes should adhere to.
    |
    */
    'claims' => [
        // App\Claims\CustomClaim::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Issue Claim
    |--------------------------------------------------------------------------
    |
    | Here you config the issue claim, weather to add it or not and what is the issuer.
    | NOTE: it will set the `iss` claim ref: https://www.rfc-editor.org/rfc/rfc7519#section-4.1.1
    |
    */
    'issuer_enabled' => env('JWT_ISSUER_ENABLED', false),
    'issuer' => env('JWT_ISSUER'),
];
