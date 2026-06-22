<?php

namespace Metafori\Archeo\Listeners;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Metafori\Archeo\Jobs\CompressPdfJob;
use Metafori\Archeo\Jobs\WatermarkPdfJob;
use Metafori\Archeo\Models\Activity;
use Metafori\Core\Models\User;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class CompressPdfOnUploadListener
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

        $jobs = [new CompressPdfJob($media->id, $user)];

        // Watermark the compressed PDF once compression finishes.
        $watermarkImage = config('archeo.watermark_image');

        if ($watermarkImage && file_exists($watermarkImage)) {
            $jobs[] = new WatermarkPdfJob($media->id, $user);
        }

        Bus::chain($jobs)->dispatch();
    }
}
