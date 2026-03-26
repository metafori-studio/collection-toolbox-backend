<?php

namespace Metafori\Archeo\Jobs;

use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Metafori\Archeo\Models\ActivityImport;
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
    ) {}

    public function handle(ActivityExcelParser $parser): void
    {
        $import = ActivityImport::create([
            'job_id' => $this->job?->getJobId(),
            'file_name' => $this->originalFileName,
            'user_id' => $this->user->id,
            'status' => 'processing',
        ]);

        try {
            $result = $parser->importFromPath($this->filePath, $import->id);

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
        } catch (Throwable $e) {
            $import->update(['status' => 'failed']);

            Notification::make()
                ->title(__('archeo::activities.notifications.import_failed.title'))
                ->body('A system error occurred during import. Please check the file format or contact support.')
                ->danger()
                ->sendToDatabase($this->user);

            throw $e;
        } finally {
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        }
    }
}
