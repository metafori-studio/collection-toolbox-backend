<?php

namespace Metafori\Etno\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Metafori\Etno\EtnoPlugin;

class EtnoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'etno') {
                return;
            }

            $panel->plugin(EtnoPlugin::make());
        });
    }

    public function boot(): void {}
}
