<?php

namespace Metafori\Core\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Carbon;
use Metafori\Core\Enums\DatePrecision;

class FuzzyDateEndField extends TextInput
{
    use Concerns\HasFuzzyDate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFuzzyDate();

        $this->dehydrateStateUsing(function (self $component, $state) {
            if (blank($state)) {
                return null;
            }

            $precision = $component->getPrecision();

            if ($precision === DatePrecision::Year) {
                return "{$state}-12-31";
            }

            if ($precision === DatePrecision::Month) {
                try {
                    return Carbon::parse($state)->endOfMonth()->toDateString();
                } catch (\Exception $e) {
                    return null;
                }
            }

            return $state;
        });
    }
}
