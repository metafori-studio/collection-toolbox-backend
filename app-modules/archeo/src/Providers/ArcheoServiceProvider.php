<?php

namespace Metafori\Archeo\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Metafori\Archeo\ArcheoPlugin;

class ArcheoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'archeo');

        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'archeo') {
                return;
            }

            $panel->plugin(ArcheoPlugin::make());
        });
    }

    public function boot(): void
    {
        //
    }
}
