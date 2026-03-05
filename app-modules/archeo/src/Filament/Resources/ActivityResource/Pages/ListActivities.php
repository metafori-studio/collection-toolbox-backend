<?php

namespace Metafori\Archeo\Filament\Resources\ActivityResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Metafori\Archeo\Filament\Resources\ActivityResource;
use Metafori\Archeo\Services\ActivityExcelParser;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('importExcel')
                ->label('Import Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('info')
                ->form([
                    FileUpload::make('file')
                        ->label('Excel File')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required()
                        ->disk('local')
                        ->directory('temp-imports'),
                ])
                ->action(function (array $data, ActivityExcelParser $parser): void {
                    $filePath = Storage::disk('local')->path($data['file']);
                    $originalFileName = basename($data['file']);

                    try {
                        $count = $parser->importFromPath($filePath, $originalFileName);

                        Notification::make()
                            ->title('Import Successful')
                            ->body("Successfully imported {$count} activities.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($data['file']);
                    }
                }),
        ];
    }
}
