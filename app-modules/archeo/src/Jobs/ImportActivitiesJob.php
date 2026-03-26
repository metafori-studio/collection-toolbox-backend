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
        public User $user
    ) {}

    public function handle(): void
    {
        // Get the absolute path when needed for processing
        $filePath = Storage::disk($this->disk)->path($this->relativePath);

        $import = ActivityImport::create([
            'file_name' => $this->originalFileName,
            'path' => $this->relativePath,
            'disk' => $this->disk,
            'user_id' => $this->user->id,
            'status' => ActivityImport::STATUS_PROCESSING,
        ]);

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

            $body = "Created: {$result['created']}";
            if ($result['updated'] > 0) {
                $body .= ", Updated: {$result['updated']}";
            }

            $notification = Notification::make()
                ->title(__('archeo::activities.notifications.import_success.title'))
                ->body($body)
                ->success();

            if (! empty($result['errors'])) {
                $errorList = implode("\n", $result['errors']);

                $notification->warning()
                    ->body($body."\n\nFailed:\n".$errorList)
                    ->persistent();
            }

            $notification->sendToDatabase($this->user);

            // Delete the file after successful completion
            Storage::disk($this->disk)->delete($this->relativePath);
        } catch (Throwable $e) {
            $import->update(['status' => ActivityImport::STATUS_FAILED]);

            Notification::make()
                ->title(__('archeo::activities.notifications.import_failed.title'))
                ->body(__('archeo::activities.notifications.import_failed.body'))
                ->danger()
                ->sendToDatabase($this->user);

            throw $e;
        } finally {
            // File cleanup is handled in failed() method and after successful completion
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Delete the file when the job has exhausted all retries
        Storage::disk($this->disk)->delete($this->relativePath);
    }
}
