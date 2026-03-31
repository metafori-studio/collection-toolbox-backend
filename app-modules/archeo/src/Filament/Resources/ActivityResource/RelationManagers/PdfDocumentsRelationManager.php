<?php

namespace Metafori\Archeo\Filament\Resources\ActivityResource\RelationManagers;

use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PdfDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'PDF Documents';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('file_name')
                    ->label(__('archeo::activities.fields.file_name')),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('archeo::activities.fields.pdfs'))
                    ->schema([
                        TextEntry::make('file_name')
                            ->label(__('archeo::activities.fields.file_name'))
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('size')
                            ->label(__('archeo::activities.fields.file_size'))
                            ->formatStateUsing(fn (Media $record): string => $this->formatFileSize($record->size)),
                        TextEntry::make('created_at')
                            ->label(__('archeo::activities.fields.uploaded_at'))
                            ->dateTime(),
                        TextEntry::make('mime_type')
                            ->label(__('archeo::activities.fields.mime_type'))
                            ->badge(),
                    ])
                    ->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('collection_name', 'pdfs'))
            ->columns([
                Tables\Columns\IconColumn::make('is_pdf')
                    ->label('')
                    ->icon('heroicon-o-document-pdf')
                    ->color('danger')
                    ->boolean(fn (Media $record): bool => $record->mime_type === 'application/pdf'),

                Tables\Columns\TextColumn::make('file_name')
                    ->label(__('archeo::activities.fields.file_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('size')
                    ->label(__('archeo::activities.fields.file_size'))
                    ->sortable()
                    ->formatStateUsing(fn (Media $record): string => $this->formatFileSize($record->size)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('archeo::activities.fields.uploaded_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\Action::make('uploadPdf')
                    ->label(__('archeo::activities.actions.add_pdf'))
                    ->modalHeading(__('archeo::activities.actions.add_pdf'))
                    ->form([
                        FileUpload::make('files')
                            ->label(__('archeo::activities.fields.pdfs'))
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk(config('archeo.pdfs_disk', 'public'))
                            ->multiple()
                            ->storeFileNamesIn('original_names')
                            ->maxSize(512000)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data): void {
                        $record = $this->getOwnerRecord();
                        $files = $data['files'] ?? [];
                        $originalNames = $data['original_names'] ?? [];
                        $targetDisk = config('archeo.pdfs_disk', 'public');

                        foreach ($files as $file) {
                            // Map original name correctly (Filament uses path as key in original_names array)
                            $originalName = $originalNames[$file] ?? basename($file);

                            if ($file instanceof UploadedFile) {
                                $record->addMedia($file)
                                    ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                                    ->usingFileName($file->getClientOriginalName())
                                    ->preservingOriginal()
                                    ->toMediaCollection('pdfs', $targetDisk);
                            } elseif (is_string($file)) {
                                // SeaweedFS Optimization: use stream to avoid circular HTTP requests or CopyObject issues
                                $stream = Storage::disk($targetDisk)->readStream($file);
                                if ($stream) {
                                    try {
                                        $record->addMediaFromStream($stream)
                                            ->usingName(pathinfo($originalName, PATHINFO_FILENAME))
                                            ->usingFileName($originalName)
                                            ->preservingOriginal()
                                            ->toMediaCollection('pdfs', $targetDisk);
                                    } finally {
                                        if (is_resource($stream)) {
                                            fclose($stream);
                                        }
                                    }
                                }
                            }
                        }
                    })
                    ->successNotificationTitle(__('archeo::activities.notifications.pdf_added')),
            ])
            ->actions([
                Actions\Action::make('download')
                    ->label(__('archeo::activities.actions.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Media $record): string => $this->getDownloadUrl($record))
                    ->openUrlInNewTab(),
                Actions\DeleteAction::make()
                    ->using(function (Media $record): void {
                        $record->delete();
                    }),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->using(function ($records): void {
                            foreach ($records as $record) {
                                $record->delete();
                            }
                        }),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    private function getDownloadUrl(Media $record): string
    {
        $driver = config("filesystems.disks.{$record->disk}.driver");

        if ($driver === 's3') {
            return $record->getTemporaryUrl(now()->addMinutes(5));
        }

        return $record->getUrl();
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
