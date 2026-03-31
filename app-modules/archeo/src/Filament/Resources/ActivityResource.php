<?php

namespace Metafori\Archeo\Filament\Resources;

use BackedEnum;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Metafori\Archeo\Filament\Infolists\Components\TextEntry;
use Metafori\Archeo\Filament\Resources\ActivityResource\Pages;
use Metafori\Archeo\Filament\Resources\ActivityResource\RelationManagers;
use Metafori\Archeo\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'activity_number';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'activity_number', 'cvs_number', 'registration_year', 'activity_type',
            'cadastral_area', 'municipality', 'position', 'district',
            'research_leader', 'institution', 'action_number',
            'site_type_original', 'size_category', 'import_id',
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity_number')
                    ->label(__('archeo::activities.fields.activity_number'))
                    ->searchable()
                    ->sortable(),

                ...array_map(fn ($field) => Tables\Columns\TextColumn::make($field)
                    ->label(__("archeo::activities.fields.{$field}"))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), [
                        'cvs_number',
                        'action_number',
                        'registration_year',
                        'district',
                        'position',
                        'research_leader',
                        'institution',
                        'cadastral_area',
                    ]),

                Tables\Columns\TextColumn::make('author_ns')
                    ->label(__('archeo::activities.fields.author_ns'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereJsonContains('author_ns', $search);
                    })
                    ->badge()
                    ->separator(',')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('activity_type')
                    ->label(__('archeo::activities.fields.activity_type'))
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('municipality')
                    ->label(__('archeo::activities.fields.municipality'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('activity_year_start')
                    ->label(__('archeo::activities.fields.year_start_short'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('has_gis_link')
                    ->boolean()
                    ->label(__('archeo::activities.fields.gis_short'))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('activity_type')
                    ->label(__('archeo::activities.fields.activity_type'))
                    ->options(fn () => static::getEloquentQuery()->distinct()->pluck('activity_type', 'activity_type')->toArray()),
                Tables\Filters\TernaryFilter::make('has_gis_link')
                    ->label(__('archeo::activities.fields.has_gis_link')),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Schemas\Components\Section::make(__('archeo::activities.sections.general'))
                    ->schema([
                        Schemas\Components\Grid::make(2)
                            ->schema([
                                TextEntry::make('activity_number')
                                    ->label(__('archeo::activities.fields.activity_number')),
                                TextEntry::make('activity_type')
                                    ->label(__('archeo::activities.fields.activity_type')),
                                TextEntry::make('cvs_number')
                                    ->label(__('archeo::activities.fields.cvs_number')),
                                TextEntry::make('action_number')
                                    ->label(__('archeo::activities.fields.action_number')),
                                TextEntry::make('activity_year_start')
                                    ->label(__('archeo::activities.fields.activity_year_start')),
                                TextEntry::make('activity_year_end')
                                    ->label(__('archeo::activities.fields.activity_year_end')),
                                TextEntry::make('registration_year')
                                    ->label(__('archeo::activities.fields.registration_year')),
                            ]),
                    ])
                    ->columnSpanFull(),

                Schemas\Components\Section::make(__('archeo::activities.sections.location'))
                    ->schema([
                        Schemas\Components\Grid::make(2)
                            ->schema([
                                TextEntry::make('municipality')
                                    ->label(__('archeo::activities.fields.municipality')),
                                TextEntry::make('cadastral_area')
                                    ->label(__('archeo::activities.fields.cadastral_area')),
                                TextEntry::make('district')
                                    ->label(__('archeo::activities.fields.district')),
                                TextEntry::make('position')
                                    ->label(__('archeo::activities.fields.position')),
                                TextEntry::make('coordinate_x')
                                    ->label(__('archeo::activities.fields.coordinate_x')),
                                TextEntry::make('coordinate_y')
                                    ->label(__('archeo::activities.fields.coordinate_y')),
                                TextEntry::make('localization_degree')
                                    ->label(__('archeo::activities.fields.localization_degree')),
                                IconEntry::make('has_gis_link')
                                    ->label(__('archeo::activities.fields.has_gis_link'))
                                    ->boolean(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Schemas\Components\Section::make(__('archeo::activities.sections.research'))
                    ->schema([
                        Schemas\Components\Grid::make(2)
                            ->schema([
                                TextEntry::make('research_leader')
                                    ->label(__('archeo::activities.fields.research_leader')),
                                TextEntry::make('institution')
                                    ->label(__('archeo::activities.fields.institution')),
                                TextEntry::make('author_ns')
                                    ->label(__('archeo::activities.fields.author_ns'))
                                    ->badge()
                                    ->separator(','),
                                TextEntry::make('dating_ns')
                                    ->label(__('archeo::activities.fields.dating_ns'))
                                    ->badge()
                                    ->separator(','),
                                TextEntry::make('dating_ceans')
                                    ->label(__('archeo::activities.fields.dating_ceans'))
                                    ->badge()
                                    ->separator(','),
                                TextEntry::make('dating_site_type')
                                    ->label(__('archeo::activities.fields.dating_site_type'))
                                    ->badge()
                                    ->separator(','),
                                TextEntry::make('site_type_original')
                                    ->label(__('archeo::activities.fields.site_type_original')),
                                TextEntry::make('size_category')
                                    ->label(__('archeo::activities.fields.size_category')),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GalleriesRelationManager::class,
            RelationManagers\AssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'view' => Pages\ViewActivity::route('/{record}'),
        ];
    }
}
