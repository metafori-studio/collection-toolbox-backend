<?php

namespace Metafori\Core\Filament\Schemas\Components;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Concerns\HasName;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Metafori\Core\Data\FuzzyDate;
use Metafori\Core\Enums\DatePrecision;
use Metafori\Core\Filament\Forms\Components\FuzzyDateEndField;
use Metafori\Core\Filament\Forms\Components\FuzzyDateStartField;

class FuzzyDateFieldset extends Fieldset
{
    use HasName;

    protected bool|Closure $isRangeable = false;

    public static function make(string|array|Htmlable|Closure|null $label = null): static
    {
        $static = app(static::class, ['label' => $label]);
        $static->configure();

        return $static;
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

    public function name(string $name): static
    {
        $this->name = $name;
        $this->statePath($name);

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatStateUsing(function ($state): array {
            if (\is_array($state)) {
                return [
                    'precision' => $state['precision'] ?? $state['start_precision'] ?? DatePrecision::Day->value,
                    'start_precision' => $state['start_precision'] ?? $state['precision'] ?? DatePrecision::Day->value,
                    'end_precision' => $state['end_precision'] ?? $state['precision'] ?? DatePrecision::Day->value,
                    'is_range' => (bool) ($state['is_range'] ?? false),
                    'start' => $state['start'] ?? null,
                    'end' => $state['end'] ?? null,
                ];
            }

            return [
                'precision' => DatePrecision::Day->value,
                'start_precision' => DatePrecision::Day->value,
                'end_precision' => DatePrecision::Day->value,
                'is_range' => false,
                'start' => null,
                'end' => null,
            ];
        });

        $this->dehydrateStateUsing(function ($state) {
            $isRange = (bool) ($state['is_range'] ?? false) && $this->isRangeable();

            if (blank($state['start'] ?? null) && (! $isRange || blank($state['end'] ?? null))) {
                return null;
            }

            $resolvePrecision = function (mixed $raw): DatePrecision {
                if ($raw instanceof DatePrecision) {
                    return $raw;
                }

                return DatePrecision::tryFrom((string) $raw) ?? DatePrecision::Day;
            };

            if (! $isRange) {
                $precision = $resolvePrecision($state['precision'] ?? $state['start_precision'] ?? null);

                return FuzzyDate::single($state['start'], $precision);
            }

            $startPrecision = $resolvePrecision($state['start_precision'] ?? $state['precision'] ?? null);
            $endPrecision = $resolvePrecision($state['end_precision'] ?? $state['precision'] ?? null);

            return FuzzyDate::range(
                start: $state['start'] ?? null,
                startPrecision: $startPrecision,
                end: $state['end'] ?? null,
                endPrecision: $endPrecision,
            );
        });

        $this->schema(fn () => [
            $this->createIsRangeComponent(),
            $this->createPrecisionComponent(),
            $this->createStartPrecisionComponent(),
            $this->createStartComponent(),
            $this->createEndPrecisionComponent(),
            $this->createEndComponent(),
        ]);

        $this->columns(2);
    }

    protected function createPrecisionComponent(): Component
    {
        return Select::make('precision')
            ->label(__('core::ui.fields.precision'))
            ->options(DatePrecision::class)
            ->default(DatePrecision::Day->value)
            ->selectablePlaceholder(false)
            ->live()
            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                $set('start_precision', $state);
                if (blank($get('end_precision'))) {
                    $set('end_precision', $state);
                }
            })
            ->visible(fn (Get $get) => ! ($this->isRangeable() && $get('is_range')));
    }

    protected function createIsRangeComponent(): Component
    {
        return Toggle::make('is_range')
            ->label(__('core::ui.fields.date_range'))
            ->live()
            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                if ($state) {
                    if (blank($get('start_precision')) && $get('precision')) {
                        $set('start_precision', $get('precision'));
                    }

                    if (blank($get('end_precision')) && $get('precision')) {
                        $set('end_precision', $get('precision'));
                    }
                }
            })
            ->visible($this->isRangeable())
            ->columnSpanFull();
    }

    protected function createStartPrecisionComponent(): Component
    {
        return Select::make('start_precision')
            ->label(__('core::ui.fields.start_precision'))
            ->options(DatePrecision::class)
            ->default(DatePrecision::Day->value)
            ->selectablePlaceholder(false)
            ->live()
            ->afterStateUpdated(function ($state, Set $set) {
                $set('precision', $state);
            })
            ->visible(fn (Get $get) => $this->isRangeable() && $get('is_range'));
    }

    protected function createEndPrecisionComponent(): Component
    {
        return Select::make('end_precision')
            ->label(__('core::ui.fields.end_precision'))
            ->options(DatePrecision::class)
            ->default(DatePrecision::Day->value)
            ->selectablePlaceholder(false)
            ->live()
            ->visible(fn (Get $get) => $this->isRangeable() && $get('is_range'));
    }

    protected function createStartComponent(): Component
    {
        return FuzzyDateStartField::make('start')
            ->label(fn (Get $get) => ($this->isRangeable() && $get('is_range')) ? __('core::ui.fields.start') : __('core::ui.fields.date'))
            ->precisionField(fn (Get $get) => ($this->isRangeable() && $get('is_range')) ? 'start_precision' : 'precision');
    }

    protected function createEndComponent(): Component
    {
        return FuzzyDateEndField::make('end')
            ->label(__('core::ui.fields.end'))
            ->precisionField(fn (Get $get) => ($this->isRangeable() && $get('is_range')) ? 'end_precision' : 'precision')
            ->hidden(fn (Get $get) => ! ($this->isRangeable() && $get('is_range')))
            ->rules([
                fn (Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                    if (! ($this->isRangeable() && $get('is_range'))) {
                        return;
                    }

                    $start = $get('start');
                    if (blank($start) || blank($value)) {
                        return;
                    }

                    try {
                        $startDate = Carbon::parse($start);
                        $endDate = Carbon::parse($value);

                        if ($startDate->greaterThanOrEqualTo($endDate)) {
                            $fail(__('core::ui.validation.end_date_must_be_after_start_date'));
                        }
                    } catch (\Exception $e) {
                        // Let invalid date formats be handled or ignored
                    }
                },
            ]);
    }

    /**
     * @param  array<string, mixed>|null  $hydratedDefaultState
     * @param  array<string, true>  $appliedStateCastPaths
     */
    public function hydrateState(?array &$hydratedDefaultState, bool $shouldCallHydrationHooks = true, bool $shouldApplyStateCasts = true, array &$appliedStateCastPaths = []): void
    {
        if ($hydratedDefaultState === null) {
            $value = $this->getRawState();
            $record = $this->getRecord();

            if ($value === null && $record instanceof Model && isset($this->name)) {
                $value = $record->getAttribute($this->name);
            }

            if ($value instanceof FuzzyDate) {
                $this->rawState([
                    'precision' => $value->startPrecision?->value ?? DatePrecision::Day->value,
                    'start_precision' => $value->startPrecision?->value ?? DatePrecision::Day->value,
                    'end_precision' => $value->endPrecision?->value ?? DatePrecision::Day->value,
                    'is_range' => $value->isRange,
                    'start' => $value->start?->toDateString(),
                    'end' => $value->end?->toDateString(),
                ]);
            }
        }

        parent::hydrateState($hydratedDefaultState, $shouldCallHydrationHooks, $shouldApplyStateCasts, $appliedStateCastPaths);
    }
}
