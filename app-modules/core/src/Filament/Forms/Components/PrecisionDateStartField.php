<?php

namespace Metafori\Core\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Carbon;

class PrecisionDateStartField extends TextInput
{
    use Concerns\HasPrecisionDate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpPrecisionDate();

        $this->dehydrateStateUsing(function (self $component, $state) {
            if (blank($state)) {
                return null;
            }

            $precision = $component->getPrecision();

            if ($precision === 'year') {
                return "{$state}-01-01";
            }

            if ($precision === 'month') {
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
