<?php

namespace Metafori\Etno\Filament\Resources\Items\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Core\Filament\Forms\Components\LocalitySelect;
use Metafori\Core\Filament\Forms\Components\PrecisionDateSection;
use Metafori\Core\Filament\Resources\KeywordResource\Schemas\KeywordForm;
use Metafori\Core\Filament\Resources\OrganizationResource\Schemas\OrganizationForm;
use Metafori\Core\Filament\Resources\PersonResource\Schemas\PersonForm;
use Metafori\Core\Models\Person;
use Metafori\Etno\Enums\AccessRight;
use Metafori\Etno\Enums\AcquisitionMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ItemFormat;
use Metafori\Etno\Enums\ItemNotation;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\SizeType;
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
                    ])
                    ->columns(2),

                Section::make('Content & Metadata')
                    ->schema([
                        TranslatableTabs::make()
                            ->schema([
                                TextInput::make('title'),
                                TextInput::make('subtitle'),
                                Textarea::make('abstract')
                                    ->rows(5),
                                TextInput::make('note'),
                            ]),
                        Select::make('keywords')
                            ->relationship('keywords', 'name')
                            ->multiple()
                            ->searchable()
                            ->reorderable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => KeywordForm::configure($schema)->getComponents())
                            ->saveRelationshipsUsing(function ($component, $state) {
                                $set = collect($state)
                                    ->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index]])
                                    ->toArray();

                                $component->getRelationship()->sync($set);
                            }),
                        Select::make('language')
                            ->options(Language::class)
                            ->searchable(),
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
                        TextInput::make('locality_note')
                            ->translatableTabs(),
                    ])
                    ->columns(1),

                Section::make('Participants')
                    ->schema([
                        Select::make('authors')
                            ->relationship('authors')
                            ->getOptionLabelFromRecordUsing(fn (Person $person) => $person->display_name)
                            ->multiple()
                            ->searchable()
                            ->reorderable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => PersonForm::configure($schema)->getComponents())
                            ->saveRelationshipsUsing(function ($component, $state) {
                                $set = collect($state)
                                    ->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index]])
                                    ->toArray();

                                $component->getRelationship()->sync($set);
                            })
                            ->columnSpanFull(),

                        Select::make('researchers')
                            ->relationship('researchers')
                            ->getOptionLabelFromRecordUsing(fn (Person $person) => $person->display_name)
                            ->multiple()
                            ->searchable()
                            ->reorderable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => PersonForm::configure($schema)->getComponents())
                            ->saveRelationshipsUsing(function ($component, $state) {
                                $set = collect($state)
                                    ->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index]])
                                    ->toArray();

                                $component->getRelationship()->sync($set);
                            })
                            ->columnSpanFull(),

                        Repeater::make('originators')
                            ->relationship('originators')
                            ->schema([
                                Select::make('person_id')
                                    ->distinct()
                                    ->relationship('person')
                                    ->getOptionLabelFromRecordUsing(fn (Person $person) => $person->display_name)
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm(fn (Schema $schema) => PersonForm::configure($schema)->getComponents())
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

                Section::make('Dates')
                    ->schema([
                        PrecisionDateSection::make('Study period')
                            ->settingsField('study_period_settings')
                            ->startField('study_period_start')
                            ->endField('study_period_end')
                            ->rangeable(),
                        PrecisionDateSection::make('Submission')
                            ->settingsField('submission_date_settings')
                            ->startField('submission_date_start')
                            ->endField('submission_date_end'),
                    ]),

                Section::make('Collection & Acquisition')
                    ->schema([
                        Select::make('institution_id')
                            ->relationship('institution', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => OrganizationForm::configure($schema))
                            ->columnSpanFull(),
                        Select::make('researchCollections')
                            ->relationship('researchCollections', 'title')
                            ->multiple()
                            ->searchable()
                            ->reorderable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => ResearchCollectionForm::configure($schema)->getComponents())
                            ->saveRelationshipsUsing(function ($component, $state) {
                                $set = collect($state)
                                    ->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index]])
                                    ->toArray();

                                $component->getRelationship()->sync($set);
                            })
                            ->columnSpanFull(),
                        Select::make('project_id')
                            ->relationship('project', 'title')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => ProjectForm::configure($schema)->getComponents())
                            ->columnSpanFull(),
                        Select::make('collection_method')
                            ->options(CollectionMethod::class)
                            ->searchable(),
                        Select::make('acquisition_method')
                            ->options(AcquisitionMethod::class)
                            ->searchable(),
                    ])
                    ->columns(2),

                Section::make('Technical Details')
                    ->schema([
                        FusedGroup::make([
                            TextInput::make('size')
                                ->requiredWith('size_type')
                                ->maxLength(255),
                            Select::make('size_type')
                                ->requiredWith('size')
                                ->options(SizeType::class)
                                ->searchable(),
                        ])
                            ->label('Size')
                            ->columns(2)
                            ->columnSpanFull(),
                        Select::make('notations')
                            ->options(ItemNotation::class)
                            ->multiple()
                            ->reorderable()
                            ->searchable(),
                        Select::make('formats')
                            ->options(ItemFormat::class)
                            ->multiple()
                            ->reorderable()
                            ->searchable(),
                    ])
                    ->columns(2),

                Section::make('Access & Licenses')
                    ->schema([
                        Select::make('access_right')
                            ->options(AccessRight::class)
                            ->searchable(),
                        Select::make('license')
                            ->options(License::class)
                            ->searchable(),
                        TextInput::make('access_right_note')
                            ->translatableTabs()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }
}
