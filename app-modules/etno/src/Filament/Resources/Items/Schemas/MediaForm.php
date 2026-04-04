<?php

namespace Metafori\Etno\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Metafori\Etno\Enums\TranscriptFormat;
use Metafori\Etno\Filament\Forms\Components\Items\TranscriptTextarea;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getComponents())
            ->columns(2);
    }

    public static function getComponents(): array
    {
        $components = [];
        $components[] = TextInput::make('name')
            ->required()
            ->columnSpanFull();

        foreach (TranscriptFormat::cases() as $format) {
            $components[] = TranscriptTextarea::make("custom_properties.transcripts.{$format->value}")
                ->label("Transcript ({$format->getLabel()})")
                ->placeholder("You can drag & drop a {$format->getLabel()} file here...");
        }

        return $components;
    }
}
