<?php

namespace Metafori\Archeo\Providers;

use Filament\Panel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Metafori\Archeo\ArcheoPlugin;
use Metafori\Archeo\Models\Activity;
use Metafori\Archeo\Models\ActivityAssignment;
use Metafori\Archeo\Policies\ActivityPolicy;
use Metafori\Core\Models\User;

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
        Gate::policy(Activity::class, ActivityPolicy::class);

        User::resolveRelationUsing('activityAssignments', function (User $user) {
            return $user->hasMany(ActivityAssignment::class);
        });
    }
}
