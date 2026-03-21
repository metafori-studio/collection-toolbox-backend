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

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
