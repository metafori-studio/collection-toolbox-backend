<?php

namespace Metafori\Etno\Filament\Resources\Items\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Core\Filament\Forms\Components\LocalitySelect;
use Metafori\Core\Filament\Forms\Components\PersonSelect;
use Metafori\Core\Filament\Forms\Components\PrecisionDateSection;
use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Core\Filament\Resources\KeywordResource\Schemas\KeywordForm;
use Metafori\Core\Filament\Resources\OrganizationResource\Schemas\OrganizationForm;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ExtentUnit;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Filament\Resources\Projects\Schemas\ProjectForm;
use Metafori\Etno\Filament\Resources\ResearchCollections\Schemas\ResearchCollectionForm;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('id')
                            ->label('ID')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn ($record) => $record !== null),
                        TextInput::make('doi')
                            ->label('DOI')
                            ->placeholder('10.xxxx/xxxx')
                            ->maxLength(255),
                        Select::make('type')
                            ->options(ItemType::class)
                            ->searchable()
                            ->columnSpanFull(),
                        Select::make('researchCollections')
                            ->relationship('researchCollections', 'title')
                            ->multiple()
                            ->searchable()
                            ->reorderable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => ResearchCollectionForm::configure($schema)->getComponents())
                            ->saveOrder()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Descriptive Information')
                    ->schema([
                        TranslatableTabs::make()
                            ->schema([
                                TextInput::make('title'),
                                TextInput::make('subtitle'),
                                Textarea::make('abstract')
                                    ->rows(5),
                                TextInput::make('content_note'),
                            ]),
                        Select::make('keywords')
                            ->relationship('keywords', 'name')
                            ->multiple()
                            ->searchable()
                            ->reorderable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => KeywordForm::configure($schema)->getComponents())
                            ->saveOrder(),
                        Select::make('language')
                            ->options(Language::class)
                            ->searchable(),
                    ])
                    ->columns(1),

                Section::make('Authors and Creators')
                    ->schema([
                        PersonSelect::make('authors')
                            ->relationship('authors')
                            ->multiple()
                            ->searchable()
                            ->reorderable()
                            ->preload()
                            ->withOptionForm()
                            ->saveOrder()
                            ->columnSpanFull(),

                        PersonSelect::make('researchers')
                            ->relationship('researchers')
                            ->multiple()
                            ->searchable()
                            ->reorderable()
                            ->preload()
                            ->withOptionForm()
                            ->saveOrder()
                            ->columnSpanFull(),

                        Repeater::make('originators')
                            ->relationship('originators')
                            ->schema([
                                PersonSelect::make('person_id')
                                    ->distinct()
                                    ->relationship('person')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->disabled(fn (Get $get) => collect($get('label'))->filter()->isNotEmpty())
                                    ->helperText('Selecting a person will disable the manual label field.')
                                    ->required(fn (Get $get) => collect($get('label'))->filter()->isEmpty()),
                                TextInput::make('label')
                                    ->maxLength(255)
                                    ->helperText('Entering a manual label will disable the person selection.')
                                    ->translatableTabs()
                                    ->live()
                                    ->disabled(fn (Get $get) => filled($get('person_id')))
                                    ->requiredOnFallbackLocale(fn (Get $get) => blank($get('person_id'))),
                            ])
                            ->defaultItems(0)
                            ->reorderableWithButtons()
                            ->orderColumn('sort_order')
                            ->columnSpanFull(),
                    ]),

                Section::make('Geographic Information')
                    ->schema([
                        Repeater::make('localities')
                            ->relationship('localities')
                            ->label('Localities')
                            ->schema([
                                LocalitySelect::make('locality')
                                    ->label('Locality')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->defaultItems(0)
                            ->reorderableWithButtons()
                            ->orderColumn('sort_order'),
                        TextInput::make('location_note')
                            ->translatableTabs(),
                    ]),

                Section::make('Technical and Format Information')
                    ->schema([
                        FusedGroup::make([
                            TextInput::make('extent')
                                ->requiredWith('extent_unit')
                                ->maxLength(255),
                            Select::make('extent_unit')
                                ->requiredWith('extent')
                                ->options(ExtentUnit::class)
                                ->sortedOptions()
                                ->searchable(),
                        ])
                            ->label('Extent')
                            ->columns(2)
                            ->columnSpanFull(),
                        Select::make('production_methods')
                            ->options(ProductionMethod::class)
                            ->multiple()
                            ->reorderable()
                            ->searchable(),
                    ])
                    ->columns(2),

                Section::make('Provenance and Research Context')
                    ->schema([
                        Select::make('institution_id')
                            ->relationship('institution', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => OrganizationForm::configure($schema))
                            ->columnSpanFull(),
                        Select::make('project_id')
                            ->relationship('project', 'title')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => ProjectForm::configure($schema)->getComponents())
                            ->columnSpanFull(),
                        Select::make('collection_method')
                            ->options(CollectionMethod::class)
                            ->sortedOptions()
                            ->searchable(),
                        Select::make('accrual_method')
                            ->options(AccrualMethod::class)
                            ->searchable(),
                        PrecisionDateSection::make('Time period')
                            ->settingsField('time_period_settings')
                            ->startField('time_period_start')
                            ->endField('time_period_end')
                            ->rangeable(),
                        PrecisionDateSection::make('Submission')
                            ->settingsField('submission_date_settings')
                            ->startField('submission_date_start')
                            ->endField('submission_date_end'),
                        PrecisionDateSection::make('Publication')
                            ->settingsField('publication_date_settings')
                            ->startField('publication_date_start')
                            ->endField('publication_date_end'),
                    ])
                    ->columns(2),

                Section::make('Rights and Access')
                    ->schema([
                        Select::make('access_rights')
                            ->options(AccessRights::class)
                            ->searchable(),
                        Select::make('license')
                            ->options(License::class)
                            ->searchable(),
                        TextInput::make('terms_of_use')
                            ->translatableTabs()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Additional Notes')
                    ->schema([
                        TextInput::make('general_note')
                            ->translatableTabs()
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }
}
