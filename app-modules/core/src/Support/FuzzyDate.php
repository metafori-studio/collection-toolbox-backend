<?php

namespace Metafori\Core\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Metafori\Core\Enums\DatePrecision;

class FuzzyDate implements Arrayable
{
    public function __construct(
        public ?Carbon $start = null,
        public ?Carbon $end = null,
        public bool $isRange = false,
        public ?DatePrecision $precision = null,
    ) {}

    public static function fromDatabase(Carbon|string|null $start, Carbon|string|null $end, ?array $settings): ?self
    {
        if ($start === null && $end === null) {
            return null;
        }

        $precision = $settings['precision'] ?? null;

        return new self(
            start: \is_string($start) ? Carbon::parse($start) : $start,
            end: \is_string($end) ? Carbon::parse($end) : $end,
            isRange: (bool) ($settings['is_range'] ?? false),
            precision: $precision instanceof DatePrecision ? $precision : DatePrecision::tryFrom((string) $precision),
        );
    }

    public function toArray(): array
    {
        return [
            'precision' => $this->precision?->value,
            'is_range' => $this->isRange,
            'start' => $this->start?->toDateString(),
            'end' => $this->end?->toDateString(),
        ];
    }
}
