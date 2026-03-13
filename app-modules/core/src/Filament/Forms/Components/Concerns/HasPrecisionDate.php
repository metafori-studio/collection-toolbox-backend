<?php

namespace Metafori\Core\Filament\Forms\Components\Concerns;

use Closure;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Illuminate\Support\Carbon;
use Metafori\Core\Enums\DatePrecision;

trait HasPrecisionDate
{
    use HasExtraInputAttributes;
    use HasPlaceholder;

    protected string|Closure|null $settingsField = null;

    protected string|Closure|null $precisionField = null;

    public function settingsField(string|Closure|null $field): static
    {
        $this->settingsField = $field;

        return $this;
    }

    public function precisionField(string|Closure|null $field): static
    {
        $this->precisionField = $field;

        return $this;
    }

    protected function setUpPrecisionDate(): void
    {
        $this->live();
        $this->dehydrated();
        $this->dehydratedWhenHidden();

        $this->afterStateHydrated(function (self $component, $state) {
            if (blank($state)) {
                return;
            }

            $precision = $component->getPrecision();

            try {
                if ($precision === DatePrecision::Year) {
                    $component->state(Carbon::parse($state)->format('Y'));
                } elseif ($precision === DatePrecision::Month) {
                    $component->state(Carbon::parse($state)->format('Y-m'));
                } else {
                    $component->state(Carbon::parse($state)->format('Y-m-d'));
                }
            } catch (\Exception $e) {
                // Keep original state if parsing fails
            }
        });
    }

    public function getPrecision(): DatePrecision
    {
        $precisionField = $this->evaluate($this->precisionField);
        if (! $precisionField) {
            return DatePrecision::Day;
        }

        return $this->evaluate(fn ($get) => $get($precisionField)) ?? DatePrecision::Day;
    }

    public function getType(): string
    {
        return match ($this->getPrecision()) {
            DatePrecision::Year => 'number',
            DatePrecision::Month => 'month',
            DatePrecision::Day => 'date',
        };
    }
}
