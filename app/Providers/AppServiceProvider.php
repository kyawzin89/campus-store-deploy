<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Throw an error when a relationship is loaded lazily (the N+1 problem)
        // outside production. Fix it with ->with('relation') or ->load('relation').
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
