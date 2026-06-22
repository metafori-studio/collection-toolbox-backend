<?php

namespace Metafori\Core\Filament\Schemas\Components;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Concerns\HasName;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Metafori\Core\Enums\DatePrecision;
use Metafori\Core\Filament\Forms\Components\PrecisionDateEndField;
use Metafori\Core\Filament\Forms\Components\PrecisionDateStartField;

class PrecisionDateSection extends Section
{
    use HasName;

    protected bool|Closure $isRangeable = false;

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

        $this->schema(fn () => [
            $this->createPrecisionComponent(),
            $this->createIsRangeComponent(),
            $this->createStartComponent(),
            $this->createEndComponent(),
        ]);

        $this->columns(fn (Get $get) => ($this->isRangeable() && $get($this->getIsRangePath())) ? 2 : 1);
    }

    protected function createPrecisionComponent(): Component
    {
        return Select::make($this->getPrecisionPath())
            ->label(__('core::ui.fields.precision'))
            ->options(DatePrecision::class)
            ->default(DatePrecision::Day)
            ->selectablePlaceholder(false)
            ->live()
            ->columnSpanFull();
    }

    protected function createIsRangeComponent(): Component
    {
        return Toggle::make($this->getIsRangePath())
            ->label(__('core::ui.fields.date_range'))
            ->live()
            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                if (! $state) {
                    $set($this->endFieldName(), $get($this->startFieldName()));
                }
            })
            ->visible($this->isRangeable())
            ->columnSpanFull();
    }

    protected function createStartComponent(): Component
    {
        return PrecisionDateStartField::make($this->startFieldName())
            ->label(fn (Get $get) => ($this->isRangeable() && $get($this->getIsRangePath())) ? __('core::ui.fields.start') : __('core::ui.fields.date'))
            ->settingsField($this->settingsFieldName())
            ->precisionField($this->getPrecisionPath())
            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                if (! $get($this->getIsRangePath())) {
                    $set($this->endFieldName(), $state);
                }
            });
    }

    protected function createEndComponent(): Component
    {
        return PrecisionDateEndField::make($this->endFieldName())
            ->label(__('core::ui.fields.end'))
            ->settingsField($this->settingsFieldName())
            ->precisionField($this->getPrecisionPath())
            ->hidden(fn (Get $get) => ! ($this->isRangeable() && $get($this->getIsRangePath())));
    }

    protected function settingsFieldName(): string
    {
        return $this->getName().'_settings';
    }

    protected function startFieldName(): string
    {
        return $this->getName().'_start';
    }

    protected function endFieldName(): string
    {
        return $this->getName().'_end';
    }

    protected function getIsRangePath(): string
    {
        return $this->settingsFieldName().'.is_range';
    }

    protected function getPrecisionPath(): string
    {
        return $this->settingsFieldName().'.precision';
    }
}
