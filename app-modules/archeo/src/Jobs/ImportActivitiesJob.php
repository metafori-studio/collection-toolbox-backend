<?php

namespace Metafori\Archeo\Jobs;

use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Metafori\Archeo\Services\ActivityExcelParser;
use Metafori\Core\Models\User;
use Throwable;

class ImportActivitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public string $originalFileName,
        public User $user
    ) {
        $this->onQueue(config('archeo.import_queue', 'default'));
    }

    public function handle(ActivityExcelParser $parser): void
    {
        try {
            $result = $parser->importFromPath($this->filePath, $this->originalFileName);

            $body = "Created: {$result['created']}";

            $notification = Notification::make()
                ->title(__('archeo::activities.notifications.import_success.title'))
                ->body($body)
                ->success();

            if (! empty($result['errors'])) {
                // Ensure errors are displayed on separate lines
                $errorList = implode("\n", $result['errors']);

                $notification->warning()
                    ->body($body."\n\nFailed:\n".$errorList)
                    ->persistent();
            }

            $notification->sendToDatabase($this->user);
        } catch (Throwable $e) {
            Notification::make()
                ->title(__('archeo::activities.notifications.import_failed.title'))
                ->body('A system error occurred during import. Please check the file format or contact support.')
                ->danger()
                ->sendToDatabase($this->user);

            throw $e;
        }
    }
}
