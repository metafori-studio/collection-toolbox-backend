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

                Forms\Components\Textarea::make('description')
                    ->label(__('archeo::activities.fields.gallery_description'))
                    ->maxLength(65535)
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('gallery_images')
                    ->label(__('archeo::activities.fields.gallery_images'))
                    ->collection('gallery_images')
                    ->disk(config('archeo.media_disk', 'local'))
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

                        TextEntry::make('description')
                            ->label(__('archeo::activities.fields.gallery_description')),
                    ])
                    ->columns(2),

                Section::make(__('archeo::activities.fields.gallery'))
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('gallery_images')
                            ->label('')
                            ->collection('gallery_images')
                            ->disk(config('archeo.media_disk', 'local'))
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
                    ->disk(config('archeo.media_disk', 'local'))
                    ->conversion('thumb')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->url(fn (Gallery $record): ?string => $record->getFirstMediaUrl('gallery_images'))
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('archeo::activities.fields.gallery_title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('archeo::activities.fields.gallery_description'))
                    ->limit(50),
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
}
