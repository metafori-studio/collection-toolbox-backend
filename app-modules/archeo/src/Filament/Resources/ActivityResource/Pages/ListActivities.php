<?php

namespace Metafori\Archeo\Filament\Resources\ActivityResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Metafori\Archeo\Filament\Resources\ActivityResource;
use Metafori\Archeo\Jobs\ImportActivitiesJob;
use Metafori\Archeo\Models\Activity;
use Metafori\Archeo\Models\ActivityImport;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('importExcel')
                ->label(__('archeo::activities.actions.import_excel.label'))
                ->icon('heroicon-o-document-arrow-up')
                ->color('info')
                ->visible(fn (): bool => auth()->user()->can('import', Activity::class))
                ->form([
                    FileUpload::make('file')
                        ->label(__('archeo::activities.actions.import_excel.fields.file'))
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->maxSize(10240) // 10MB
                        ->required()
                        ->disk('local')
                        ->directory('temp-imports'),
                ])
                ->action(function (array $data): void {
                    $originalFileName = basename($data['file']);
                    $import = ActivityImport::create([
                        'file_name' => $originalFileName,
                        'path' => $data['file'],
                        'disk' => 'local',
                        'user_id' => auth()->id(),
                        'status' => ActivityImport::STATUS_PROCESSING,
                    ]);

                    ImportActivitiesJob::dispatch(
                        'local', // disk name
                        $data['file'], // relative path
                        $originalFileName,
                        auth()->user(),
                        $import->id,
                    );

                    Notification::make()
                        ->title(__('archeo::activities.notifications.import_queued.title'))
                        ->body(__('archeo::activities.notifications.import_queued.body'))
                        ->info()
                        ->send();
                }),
        ];
    }
}
