<?php

namespace Metafori\Archeo\Listeners;

use Illuminate\Support\Facades\Auth;
use Metafori\Archeo\Jobs\WatermarkPdfJob;
use Metafori\Archeo\Models\Activity;
use Metafori\Core\Models\User;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class WatermarkPdfOnUploadListener
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        if (
            $media->model_type !== Activity::class
            || $media->collection_name !== 'pdfs'
            || $media->mime_type !== 'application/pdf'
        ) {
            return;
        }

        /** @var User|null $user */
        $user = Auth::user();

        WatermarkPdfJob::dispatch($media->id, $user);
    }
}
