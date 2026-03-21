<?php

namespace Metafori\Core\Filament\Forms\Components;

use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Region;

class LocalitySelect extends MorphToSelect
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->types([
            Type::make(Country::class)->titleAttribute('name')->label(__('core::types.Country')),
            Type::make(Region::class)->titleAttribute('name')->label(__('core::types.Region')),
            Type::make(District::class)->titleAttribute('name')->label(__('core::types.District')),
            Type::make(Municipality::class)->titleAttribute('name')->label(__('core::types.Municipality')),
            Type::make(MunicipalityPart::class)->titleAttribute('name')->label(__('core::types.MunicipalityPart')),
            Type::make(Location::class)->titleAttribute('name')->label(__('core::types.Location')),
        ]);
    }
}
