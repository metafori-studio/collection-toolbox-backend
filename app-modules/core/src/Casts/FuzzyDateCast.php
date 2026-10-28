<?php

namespace Metafori\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Metafori\Core\Data\FuzzyDate;

/**
 * @implements CastsAttributes<FuzzyDate|null, FuzzyDate|null>
 */
class FuzzyDateCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  array<string, mixed>  $attributes
     */
    public function get($model, string $key, mixed $value, array $attributes): ?FuzzyDate
    {
        $start = $attributes[$key.'_start'] ?? null;
        $end = $attributes[$key.'_end'] ?? null;
        $settings = $model->getAttribute($key.'_settings');

        return FuzzyDate::fromDatabase($start, $end, $settings);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function set($model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [
                $key.'_start' => null,
                $key.'_end' => null,
                $key.'_settings' => null,
            ];
        }

        if (! $value instanceof FuzzyDate) {
            throw new InvalidArgumentException('Value must be an instance of FuzzyDate or null.');
        }

        $settings = $value->isRange ? [
            'is_range' => true,
            'start_precision' => $value->startPrecision?->value,
            'end_precision' => $value->endPrecision?->value,
        ] : [
            'is_range' => false,
            'precision' => $value->startPrecision?->value,
        ];

        return [
            $key.'_start' => $value->start?->toDateString(),
            $key.'_end' => $value->end?->toDateString(),
            $key.'_settings' => \json_encode($settings),
        ];
    }
}
