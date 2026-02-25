<?php

namespace Metafori\Core\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Carbon;

class PrecisionDateEndField extends TextInput
{
    use Concerns\HasPrecisionDate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpPrecisionDate();

        $this->dehydrateStateUsing(function (self $component, $state) {
            $settingsField = $this->evaluate($this->settingsField);

            if ($settingsField && ! $component->evaluate(fn ($get) => $get("{$settingsField}.is_range"))) {
                $state = $component->evaluate(fn ($get) => $get(str_replace('_end', '_start', $component->getName())));
            }

            if (blank($state)) {
                return null;
            }

            $precision = $component->getPrecision();

            if ($precision === 'year') {
                return "{$state}-12-31";
            }

            if ($precision === 'month') {
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
