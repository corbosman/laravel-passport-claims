# laravel-passport-claims

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
![build](https://github.com/corbosman/laravel-passport-claims/workflows/build/badge.svg?branch=master)
![license](https://img.shields.io/packagist/l/corbosman/laravel-passport-claims)

This package allows you to add claims to Laravel Passport JWT Tokens. If you have questions or comments, please open an issue. 

## Installation

There are 2 versions of this package, ^1 and ^2. You need to use ^2 if you use Passport 10.1.0 or higher. In that version Passport made significant changes to how the JWT is initiated. They did this because the upstream JWT library was causing errors in Passport installations. So first check which version of Passport you are using, then pick either ^1 or ^2. 

Via Composer

``` bash
$ composer require corbosman/laravel-passport-claims ^2
```



## Usage

This package sends the AccessToken class through a pipeline of classes to collect all the claims, similar to how laravel middleware works. Each class adds a claim to the token. For each claim that you want to add, you need to create a class like the example below. You can of course add multiple claims in a single class as well. 

You can use an artisan command to generate a class for you. Just provide a path from the root of your app folder. The example below will create a class app/Claims/CustomClaim.php

```bash
$ php artisan claim:generate Claims/CustomClaim
```

```php
<?php

namespace App\Claims;

class CustomClaim
{
    public function handle($token, $next)
    {
        $token->addClaim('my-claim', 'my custom claim data');

        return $next($token);
    }
}
```

Because the Passport AccessToken is sent through the pipeline, you have access to methods on the AccessToken class. This is useful if you want to derive information from the token. For instance, look up user data based on the token user identifier. You can check the AccessToken class to see all the methods you can use. 

```php
<?php

namespace App\Claims;

use App\User;

class CustomClaim
{
    public function handle($token, $next)
    {
        $user = User::find($token->getUserIdentifier());

        $token->addClaim('email', $user->email);

        return $next($token);
    }
}
```

### config

To tell this package which claims you want to add, you need to publish the config file and add a list of all your classes. To publish the config file, run the following command after installing this package. 

```shell
php artisan vendor:publish --provider="CorBosman\Passport\ServiceProvider"
```

The config file will look like this.

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Claim Classes
    |--------------------------------------------------------------------------
    |
    | Here you can add an array of classes that will each be called to add
    | claims to the passport JWT token. See the readme for the interface that
    | these classes should adhere to.
    |
    */
    'claims' => [
        App\Claims\MyCustomClaim::class,
        App\Claims\MyOtherCustomClaim::class
    ]
];
```

### middleware

You can set a middleware on a route that checks for the existence of a specific claim. Add the middleware to your \App\Http\Kernel.php class:

```php
    protected $routeMiddleware = [
        'claim' => \CorBosman\Passport\Http\Middleware\CheckForClaim::class,
    ];
```

Then assign this middleware to a route. Generally you would also add a passport middleware that checks for a valid token. 

```php
Route::middleware(['client', 'claim:my-claim'])->get('my-protected-route', function () {
    return 'protected by claim';
});
```

You can also check if the claim matches a specific value.

 ```php
 Route::middleware(['client', 'claim:my-claim,foobar'])->get('my-protected-route', function () {
     return 'protected by claim with foobar as its value';
 });
 ```

### Formatters

This package also allows you to configure custom Formatters. Formatters can be used to modify existing claims. You could even use them to add claims. A common reason to opt for a custom Formatter is to change the DateTime fields in the JWT from floats back to integers. Due to a change in a library, JWTs are now issued with float values, which breaks compatibility with virtually all other JWT libraries. If you run into that problem, all you have to do is add the following to the passport-claims.php config file:

```php
   'formatters' => [
        \Lcobucci\JWT\Encoding\UnifyAudience::class,
        \Lcobucci\JWT\Encoding\UnixTimestampDates::class,
    ]
```

This swaps out the microsecond formatter with the old unix timestamp formatter. You're of course also free to add any other custom claim formatters. 


## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](contributing.md) for details.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [Cor Bosman][link-author]

## License

Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/corbosman/laravel-passport-claims.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/corbosman/laravel-passport-claims.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[ico-build]: https://github.com/corbosman/laravel-passport-claims/workflows/laravel-passport-claims/badge.svg

[link-packagist]: https://packagist.org/packages/corbosman/laravel-passport-claims
[link-downloads]: https://packagist.org/packages/corbosman/laravel-passport-claims
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/corbosman
