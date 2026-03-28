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
use Metafori\Etno\Filament\Schemas\Components\Concerns\HasInheritable;

class DescriptiveInformationSection extends Section
{
    use HasInheritable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Descriptive Information')
            ->schema(fn () => [
                TranslatableTabs::make()
                    ->schema([
                        TitleInput::make('title')
                            ->inheritable($this->inheritable),
                        SubtitleInput::make('subtitle')
                            ->inheritable($this->inheritable),
                        AbstractInput::make('abstract')
                            ->inheritable($this->inheritable),
                        ContentNoteInput::make('content_note')
                            ->inheritable($this->inheritable),
                    ]),
                KeywordsSelect::make('keywords')
                    ->inheritable($this->inheritable),
                LanguageSelect::make('language')
                    ->inheritable($this->inheritable),
            ])
            ->collapsible()
            ->columns(1);
    }
}
