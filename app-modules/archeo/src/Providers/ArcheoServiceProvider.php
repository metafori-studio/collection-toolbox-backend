<?php

namespace Metafori\Archeo\Providers;

use Filament\Panel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Metafori\Archeo\ArcheoPlugin;
use Metafori\Archeo\Listeners\CompressPdfOnUploadListener;
/* use Metafori\Archeo\Listeners\WatermarkPdfOnUploadListener; */
use Metafori\Archeo\Models\Activity;
use Metafori\Archeo\Models\ActivityAssignment;
use Metafori\Archeo\Observers\ActivityObserver;
use Metafori\Core\Models\User;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

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

        Activity::observe(ActivityObserver::class);

        Event::listen(MediaHasBeenAddedEvent::class, CompressPdfOnUploadListener::class);
        /* Event::listen(MediaHasBeenAddedEvent::class, WatermarkPdfOnUploadListener::class); */
    }
}
