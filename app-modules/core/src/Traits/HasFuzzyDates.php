<?php

namespace Metafori\Core\Traits;

use Metafori\Core\Data\FuzzyDate;

trait HasFuzzyDates
{
    /**
     * Get the casts array, automatically registering underlying date and settings casts for FuzzyDate attributes.
     *
     * @return array<string, string>
     */
    public function getCasts(): array
    {
        $casts = parent::getCasts();

        foreach ($casts as $key => $cast) {
            if ($cast === FuzzyDate::class) {
                $casts[$key.'_start'] ??= 'date';
                $casts[$key.'_end'] ??= 'date';
                $casts[$key.'_settings'] ??= 'array';
            }
        }

        return $casts;
    }
}
