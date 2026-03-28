<?php

namespace Metafori\Archeo\Jobs;

use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Metafori\Archeo\Models\ActivityImport;
use Metafori\Core\Models\User;
use Throwable;

class ImportActivitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes timeout for Excel files

    public int $tries = 3; // Number of times the job may be attempted

    public int $backoff = 60; // Seconds to wait before retrying

    public function __construct(
        public string $disk,
        public string $relativePath,
        public string $originalFileName,
        public User $user,
        public ?int $importId = null,
    ) {}

    public function handle(): void
    {
        // Get the absolute path when needed for processing
        $filePath = Storage::disk($this->disk)->path($this->relativePath);

        $import = $this->importId
            ? ActivityImport::find($this->importId)
            : null;

        if (! $import) {
            $import = ActivityImport::create([
                'file_name' => $this->originalFileName,
                'path' => $this->relativePath,
                'disk' => $this->disk,
                'user_id' => $this->user->id,
                'status' => ActivityImport::STATUS_PROCESSING,
            ]);
            $this->importId = $import->id;
        }

        try {
            // Process the file using the disk and relative path
            // This would typically involve reading the file via Storage::disk($this->disk)->get($this->relativePath)
            // or using the absolute path for libraries that require it

            // For now, we'll simulate successful processing
            $result = [
                'created' => 0,
                'updated' => 0,
                'errors' => [],
            ];

            $import->update(['status' => ActivityImport::STATUS_COMPLETE]);

            $hasUpdated = $result['updated'] > 0;
            $hasErrors = ! empty($result['errors']);

            $bodyKey = 'archeo::activities.notifications.import_success.';
            $bodyKey .= match (true) {
                $hasUpdated && $hasErrors => 'body_with_updated_and_errors',
                $hasUpdated => 'body_with_updated',
                $hasErrors => 'body_with_errors',
                default => 'body',
            };

            $bodyParams = ['created' => $result['created']];
            if ($hasUpdated) {
                $bodyParams['updated'] = $result['updated'];
            }
            if ($hasErrors) {
                $bodyParams['errors'] = implode("\n", $result['errors']);
            }

            $notification = Notification::make()
                ->title(__('archeo::activities.notifications.import_success.title'))
                ->body(__($bodyKey, $bodyParams))
                ->success();

            if ($hasErrors) {
                $notification->warning()
                    ->persistent();
            }

            $notification->sendToDatabase($this->user);

            // Delete the file after successful completion
            Storage::disk($this->disk)->delete($this->relativePath);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(Throwable $exception): void
    {
        $import = $this->importId
            ? ActivityImport::find($this->importId)
            : null;

        if ($import && $import->status === ActivityImport::STATUS_PROCESSING) {
            $import->update(['status' => ActivityImport::STATUS_FAILED]);
        }

        Notification::make()
            ->title(__('archeo::activities.notifications.import_failed.title'))
            ->body(__('archeo::activities.notifications.import_failed.body'))
            ->danger()
            ->sendToDatabase($this->user);

        Storage::disk($this->disk)->delete($this->relativePath);
    }
}
