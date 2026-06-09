<?php

namespace Metafori\Art\Providers;

use Illuminate\Support\ServiceProvider;
use Metafori\Art\Database\Seeders\DatabaseSeeder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('DatabaseSeeder', DatabaseSeeder::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
