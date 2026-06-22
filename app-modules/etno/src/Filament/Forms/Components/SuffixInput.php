<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Validation\Rules\Unique;
use Metafori\Etno\Filament\Contracts\HasDocument;

class SuffixInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.fields.composite_id'))
            ->prefix(fn (HasDocument $livewire) => $livewire->getDocument()->id)
            ->required()
            ->unique(
                ignoreRecord: true,
                modifyRuleUsing: fn (Unique $rule, HasDocument $livewire) => $rule
                    ->where('document_id', $livewire->getDocument()->id)
                    ->withoutTrashed()
            )
            ->maxLength(255);
    }
}
