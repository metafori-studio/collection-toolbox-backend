<?php

namespace Metafori\Archeo\Filament\Resources;

use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Metafori\Archeo\Filament\Resources\ActivityResource\Pages;
use Metafori\Archeo\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Schemas\Components\Section::make('General Information')
                    ->schema([
                        Schemas\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('activity_number')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('activity_type')
                                    ->required(),
                                Forms\Components\TextInput::make('cvs_number')
                                    ->numeric()
                                    ->required(),
                            ]),
                        Schemas\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('activity_year_start')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('activity_year_end')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('registration_year')
                                    ->numeric(),
                            ]),
                        Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('action_number'),
                                Forms\Components\TextInput::make('file_name')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),

                Schemas\Components\Section::make('Location Details')
                    ->schema([
                        Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('municipality'),
                                Forms\Components\TextInput::make('cadastral_area'),
                                Forms\Components\TextInput::make('district'),
                                Forms\Components\TextInput::make('position'),
                            ]),
                        Schemas\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('localization_degree')
                                    ->numeric(),
                                Forms\Components\TextInput::make('coordinate_x')
                                    ->numeric()
                                    ->step('0.000001'),
                                Forms\Components\TextInput::make('coordinate_y')
                                    ->numeric()
                                    ->step('0.000001'),
                            ]),
                        Forms\Components\Toggle::make('has_gis_link')
                            ->label('Has GIS Link'),
                    ]),

                Schemas\Components\Section::make('Research & Dating')
                    ->schema([
                        Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('research_leader')
                                    ->required(),
                                Forms\Components\TextInput::make('institution'),
                                Forms\Components\Textarea::make('author_ns')
                                    ->label('Author - NS')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\TagsInput::make('dating_ns')
                            ->label('Dating - NS'),
                        Forms\Components\TagsInput::make('dating_ceans')
                            ->label('Dating - CEANS'),
                        Forms\Components\TagsInput::make('dating_site_type')
                            ->label('Dating - Site Type'),
                        Forms\Components\TextInput::make('site_type_original'),
                        Forms\Components\TextInput::make('size_category')
                            ->required(),
                    ]),

                Schemas\Components\Section::make('Attachments')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('attachments')
                            ->collection('activity_attachments')
                            ->multiple()
                            ->reorderable()
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cvs_number')
                    ->label('ČVS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity_type')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('municipality')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cadastral_area')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('activity_year_start')
                    ->label('Year Start')
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity_year_end')
                    ->label('Year End')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('has_gis_link')
                    ->boolean()
                    ->label('GIS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('activity_type'),
                Tables\Filters\TernaryFilter::make('has_gis_link'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
