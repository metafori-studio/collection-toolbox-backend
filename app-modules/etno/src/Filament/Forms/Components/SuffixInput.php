<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component;

class SuffixInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Composite ID')
            ->prefix(fn (Component $livewire) => $livewire->parentRecord?->id)
            ->required()
            ->unique(
                ignoreRecord: true,
                modifyRuleUsing: fn (Unique $rule, Component $livewire) => $rule
                    ->where(fn (Builder $query) => $query
                        ->where('document_id', $livewire->parentRecord?->id)
                        ->whereNull('deleted_at')
                    )
            )
            ->maxLength(255);
    }
}
