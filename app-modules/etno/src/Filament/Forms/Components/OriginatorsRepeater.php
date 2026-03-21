<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Metafori\Core\Filament\Forms\Components\PersonSelect;

class OriginatorsRepeater extends Repeater
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Originators')
            ->relationship('originators')
            ->schema([
                PersonSelect::make('person_id')
                    ->distinct()
                    ->relationship('person')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->withOptionForm()
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
            ->orderColumn('sort_order');
    }
}
