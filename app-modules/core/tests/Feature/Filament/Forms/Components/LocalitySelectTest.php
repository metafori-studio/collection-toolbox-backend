<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Core\Filament\Forms\Components\LocalitySelect;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\Location;

uses(RefreshDatabase::class);

it('does not include location by default', function () {
    $component = LocalitySelect::make('locality');

    $types = $component->getTypes();

    expect($types)->not->toHaveKey((new Location)->getMorphClass());
    expect($types)->toHaveKey((new Country)->getMorphClass());
});

it('includes location when includeLocation is set', function () {
    $component = LocalitySelect::make('locality')
        ->includeLocation();

    $types = $component->getTypes();

    expect($types)->toHaveKey((new Location)->getMorphClass());
});
