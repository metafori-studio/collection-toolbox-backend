<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\AbstractInput;
use Metafori\Etno\Filament\Forms\Components\ContentNoteInput;
use Metafori\Etno\Filament\Forms\Components\KeywordsSelect;
use Metafori\Etno\Filament\Forms\Components\LanguageSelect;
use Metafori\Etno\Filament\Forms\Components\SubtitleInput;
use Metafori\Etno\Filament\Forms\Components\TitleInput;

class DescriptiveInformationSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Descriptive Information')
            ->schema([
                TranslatableTabs::make()
                    ->schema([
                        TitleInput::make('title'),
                        SubtitleInput::make('subtitle'),
                        AbstractInput::make('abstract'),
                        ContentNoteInput::make('content_note'),
                    ]),
                KeywordsSelect::make('keywords'),
                LanguageSelect::make('language'),
            ])
            ->columns(1);
    }
}
