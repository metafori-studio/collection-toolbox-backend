<?php

namespace Metafori\Archeo\Filament\Resources\ActivityResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Metafori\Archeo\Models\Gallery;

class GalleriesRelationManager extends RelationManager
{
    protected static string $relationship = 'galleries';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('archeo::activities.fields.gallery_title'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('gallery_images')
                    ->label(__('archeo::activities.fields.gallery_images'))
                    ->collection('gallery_images')
                    ->disk(config('archeo.media_disk', Gallery::DEFAULT_MEDIA_DISK))
                    ->multiple()
                    ->reorderable()
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('archeo::activities.fields.gallery_title'))
                            ->weight('bold')
                            ->size('lg'),
                    ])
                    ->columns(1),

                Section::make(__('archeo::activities.fields.gallery'))
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('gallery_images')
                            ->label('')
                            ->collection('gallery_images')
                            ->disk(config('archeo.media_disk', Gallery::DEFAULT_MEDIA_DISK))
                            ->conversion('thumb')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery_images')
                    ->label('')
                    ->collection('gallery_images')
                    ->disk(config('archeo.media_disk', Gallery::DEFAULT_MEDIA_DISK))
                    ->conversion('thumb')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->url(fn (Gallery $record): ?string => $record->getFirstMediaUrl('gallery_images')),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('archeo::activities.fields.gallery_title'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
