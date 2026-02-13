<?php

namespace Metafori\Archeo;

use Filament\Contracts\Plugin;
use Filament\Panel;

class ArcheoPlugin implements Plugin
{
    public function getId(): string
    {
        return 'archeo';
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
                for: 'Metafori\\Etno\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/Filament/Pages',
                for: 'Metafori\\Etno\\Filament\\Pages',
            )
            ->discoverWidgets(
                in: __DIR__.'/Filament/Widgets',
                for: 'Metafori\\Etno\\Filament\\Widgets',
            );
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
