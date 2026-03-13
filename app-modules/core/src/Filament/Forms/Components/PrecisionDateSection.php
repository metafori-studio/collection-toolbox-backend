<?php

namespace Metafori\Core\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Metafori\Core\Enums\DatePrecision;

class PrecisionDateSection extends Section
{
    protected string|Closure|null $settingsField = null;

    protected string|Closure|null $startField = null;

    protected string|Closure|null $endField = null;

    protected bool|Closure $isRangeable = false;

    public function settingsField(string|Closure|null $field): static
    {
        $this->settingsField = $field;

        return $this;
    }

    public function startField(string|Closure|null $field): static
    {
        $this->startField = $field;

        return $this;
    }

    public function endField(string|Closure|null $field): static
    {
        $this->endField = $field;

        return $this;
    }

    public function rangeable(bool|Closure $condition = true): static
    {
        $this->isRangeable = $condition;

        return $this;
    }

    public function isRangeable(): bool
    {
        return (bool) $this->evaluate($this->isRangeable);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterHeader(fn (PrecisionDateSection $component) => [
            Select::make("{$component->evaluate($this->settingsField)}.precision")
                ->options(DatePrecision::class)
                ->inlineLabel()
                ->default(DatePrecision::Day)
                ->selectablePlaceholder(false)
                ->live(),
        ]);

        $this->schema(fn (PrecisionDateSection $component) => [
            Toggle::make("{$component->evaluate($this->settingsField)}.is_range")
                ->label('Date range')
                ->live()
                ->afterStateUpdated(function ($state, $set, $get) use ($component) {
                    if (! $state) {
                        $set($component->evaluate($this->endField), $get($component->evaluate($this->startField)));
                    }
                })
                ->visible($this->isRangeable())
                ->columnSpanFull(),
            PrecisionDateStartField::make($component->evaluate($this->startField))
                ->label(fn ($get) => ($this->isRangeable() && $get("{$component->evaluate($this->settingsField)}.is_range")) ? 'Start' : 'Date')
                ->settingsField($component->evaluate($this->settingsField))
                ->precisionField("{$component->evaluate($this->settingsField)}.precision")
                ->afterStateUpdated(function ($state, $set, $get) use ($component) {
                    if (! $get("{$component->evaluate($this->settingsField)}.is_range")) {
                        $set($component->evaluate($this->endField), $state);
                    }
                }),
            PrecisionDateEndField::make($component->evaluate($this->endField))
                ->label('End')
                ->settingsField($component->evaluate($this->settingsField))
                ->precisionField("{$component->evaluate($this->settingsField)}.precision")
                ->hidden(fn ($get) => ! ($this->isRangeable() && $get("{$component->evaluate($this->settingsField)}.is_range"))),
        ]);

        $this->columns(fn ($get) => ($this->isRangeable() && $get("{$this->evaluate($this->settingsField)}.is_range")) ? 2 : 1);
    }
}
