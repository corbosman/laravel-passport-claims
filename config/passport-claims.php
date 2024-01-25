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
    | JWT Custom Builder
    |--------------------------------------------------------------------------
    |
    | Here you can change which class implements the convertToJWT method.
    | The class requires method convertToJWT() : Token to return a custom
    | token builder for the pipeline. It must be a private function.
    |
    */
    'builder' => \CorBosman\Passport\AccessToken::class
];
