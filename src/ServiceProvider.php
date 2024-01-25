<?php

namespace CorBosman\Passport;

use CorBosman\Passport\Console\ClaimGenerator;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;
use Laravel\Passport\Passport;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot()
    {
        if (method_exists(Passport::class, 'useAccessTokenEntity')) {
            // I'm actually a little confused about this I may need a better way to test this
            Passport::useAccessTokenEntity(config('passport-claims.builder', AccessToken::class));
        } else {
            $this->app->bind(PassportAccessTokenRepository::class, AccessTokenRepository::class);
        }

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function provides()
    {
        return ['laravel-passport-claims'];
    }

    protected function bootForConsole()
    {
        $this->publishes([
            __DIR__ . '/../config/passport-claims.php' => config_path('passport-claims.php'),
        ]);

        $this->commands([ClaimGenerator::class]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/passport-claims.php', 'passport-claims');
    }
}
