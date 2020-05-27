<?php

namespace CorBosman\Passport;

use CorBosman\Passport\Console\ClaimGenerator;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot()
    {
        $this->app->bind(PassportAccessTokenRepository::class, AccessTokenRepository::class);

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
}
