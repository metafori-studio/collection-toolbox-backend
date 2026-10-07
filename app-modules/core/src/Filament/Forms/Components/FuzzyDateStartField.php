<?php

namespace Metafori\Core\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Carbon;
use Metafori\Core\Enums\DatePrecision;

class FuzzyDateStartField extends TextInput
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
                return "{$state}-01-01";
            }

            if ($precision === DatePrecision::Month) {
                try {
                    return Carbon::parse($state)->startOfMonth()->toDateString();
                } catch (\Exception $e) {
                    return null;
                }
            }

            return $state;
        });
    }
}
