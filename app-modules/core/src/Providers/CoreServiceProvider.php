<?php

namespace Metafori\Core\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Metafori\Core\CorePlugin;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            $panel->plugin(CorePlugin::make());
        });
    }

    public function boot(): void {}
}
