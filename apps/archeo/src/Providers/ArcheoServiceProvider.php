<?php

namespace Metafori\Archeo\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Metafori\Archeo\Listeners\CompressPdfOnUploadListener;
use Metafori\Archeo\Models\Activity;
use Metafori\Archeo\Models\ActivityAssignment;
use Metafori\Archeo\Observers\ActivityObserver;
use Metafori\Core\Models\User;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class ArcheoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
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
    }
}
