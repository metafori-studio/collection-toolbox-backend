<?php

namespace Metafori\Art\Providers;

use Illuminate\Support\ServiceProvider;
use Metafori\Art\Database\Seeders\DatabaseSeeder;
use Metafori\Art\Models\ArtworkAuthor;
use Metafori\Art\Observers\ArtworkAuthorObserver;

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
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'art');

        ArtworkAuthor::observe(ArtworkAuthorObserver::class);
    }
}
