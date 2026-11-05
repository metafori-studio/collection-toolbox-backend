<?php

namespace Metafori\Core\Data;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use JsonSerializable;
use Metafori\Core\Casts\FuzzyDateCast;
use Metafori\Core\Enums\DatePrecision;

class FuzzyDate implements Arrayable, Castable, JsonSerializable
{
    public function __construct(
        public ?Carbon $start = null,
        public ?Carbon $end = null,
        public bool $isRange = false,
        public ?DatePrecision $startPrecision = null,
        public ?DatePrecision $endPrecision = null,
    ) {}

    public static function single(Carbon|string $date, DatePrecision|string $precision): self
    {
        $baseDate = \is_string($date) ? Carbon::parse($date) : $date->copy();
        $parsedPrecision = $precision instanceof DatePrecision ? $precision : DatePrecision::from((string) $precision);

        $start = match ($parsedPrecision) {
            DatePrecision::Year => $baseDate->copy()->startOfYear(),
            DatePrecision::Month => $baseDate->copy()->startOfMonth(),
            DatePrecision::Day => $baseDate->copy()->startOfDay(),
        };

        $end = match ($parsedPrecision) {
            DatePrecision::Year => $baseDate->copy()->endOfYear(),
            DatePrecision::Month => $baseDate->copy()->endOfMonth(),
            DatePrecision::Day => $baseDate->copy()->endOfDay(),
        };

        return new self(
            start: $start,
            end: $end,
            isRange: false,
            startPrecision: $parsedPrecision,
            endPrecision: $parsedPrecision,
        );
    }

    public static function range(
        Carbon|string|null $start,
        DatePrecision|string $startPrecision,
        Carbon|string|null $end,
        DatePrecision|string $endPrecision,
    ): self {
        $parsedStart = \is_string($start) ? Carbon::parse($start) : $start;
        $parsedEnd = \is_string($end) ? Carbon::parse($end) : $end;
        $parsedStartPrecision = $startPrecision instanceof DatePrecision ? $startPrecision : DatePrecision::from((string) $startPrecision);
        $parsedEndPrecision = $endPrecision instanceof DatePrecision ? $endPrecision : DatePrecision::from((string) $endPrecision);

        return new self(
            start: $parsedStart,
            end: $parsedEnd,
            isRange: true,
            startPrecision: $parsedStartPrecision,
            endPrecision: $parsedEndPrecision,
        );
    }

    public static function castUsing(array $arguments): string
    {
        return FuzzyDateCast::class;
    }

    public static function fromDatabase(Carbon|string|null $start, Carbon|string|null $end, ?array $settings): ?self
    {
        if ($start === null && $end === null) {
            return null;
        }

        $resolvePrecision = static function (mixed $precision): ?DatePrecision {
            if ($precision instanceof DatePrecision) {
                return $precision;
            }

            return $precision ? DatePrecision::tryFrom((string) $precision) : null;
        };

        $isRange = (bool) ($settings['is_range'] ?? false);

        if (! $isRange) {
            $precision = $resolvePrecision($settings['precision'] ?? null);
            $startPrecision = $precision;
            $endPrecision = $precision;
        } else {
            $startPrecision = $resolvePrecision($settings['start_precision'] ?? null);
            $endPrecision = $resolvePrecision($settings['end_precision'] ?? null);
        }

        return new self(
            start: \is_string($start) ? Carbon::parse($start) : $start,
            end: \is_string($end) ? Carbon::parse($end) : $end,
            isRange: $isRange,
            startPrecision: $startPrecision,
            endPrecision: $endPrecision,
        );
    }

    public function toArray(): array
    {
        if (! $this->isRange) {
            return [
                'is_range' => false,
                'date' => $this->start?->toDateString(),
                'precision' => $this->startPrecision?->value,
            ];
        }

        return [
            'is_range' => true,
            'start' => [
                'date' => $this->start?->toDateString(),
                'precision' => $this->startPrecision?->value,
            ],
            'end' => [
                'date' => $this->end?->toDateString(),
                'precision' => $this->endPrecision?->value,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
