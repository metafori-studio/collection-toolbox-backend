<?php

namespace Metafori\Archeo\Providers;

use Filament\Panel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Metafori\Archeo\ArcheoPlugin;
use Metafori\Archeo\Models\ActivityAssignment;
use Metafori\Core\Models\User;

class ArcheoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'archeo') {
                return;
            }

            $panel->plugin(ArcheoPlugin::make());
        });
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'archeo');

        User::resolveRelationUsing('activityAssignments', function (User $user) {
            return $user->hasMany(ActivityAssignment::class);
        });

        Gate::before(function (User $user, string $ability) {
            if ($user->isAdministrator()) {
                return true;
            }
        });
    }
}
