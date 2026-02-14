<?php

namespace Metafori\Core;

use Filament\Contracts\Plugin;
use Filament\Panel;

class CorePlugin implements Plugin
{
    public function getId(): string
    {
        return 'core';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(
                in: __DIR__.'/Filament/Resources',
                for: 'Metafori\\Core\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/Filament/Pages',
                for: 'Metafori\\Core\\Filament\\Pages',
            )
            ->discoverWidgets(
                in: __DIR__.'/Filament/Widgets',
                for: 'Metafori\\Core\\Filament\\Widgets',
            );
    }

    public function boot(Panel $panel): void {}
}
