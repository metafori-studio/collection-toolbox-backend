<?php

namespace Metafori\Core\Filament\Forms\Components;

use Closure;
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
    protected bool|Closure $includeLocation = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->types(function () {
            $types = [];

            $localityTypes = [
                Country::class,
                Region::class,
                District::class,
                Municipality::class,
                MunicipalityPart::class,
            ];

            if ($this->shouldIncludeLocation()) {
                $localityTypes[] = Location::class;
            }

            foreach ($localityTypes as $class) {
                $types[] = match ($class) {
                    Country::class => Type::make(Country::class)
                        ->titleAttribute('name')
                        ->label(__('core::types.Country')),
                    Region::class => Type::make(Region::class)
                        ->titleAttribute('name')
                        ->label(__('core::types.Region'))
                        ->modifyOptionsQueryUsing(fn ($query) => $query->with('country'))
                        ->getOptionLabelFromRecordUsing(fn (Region $record) => $record->name." ({$record->country->name})"),
                    District::class => Type::make(District::class)
                        ->titleAttribute('name')
                        ->label(__('core::types.District'))
                        ->modifyOptionsQueryUsing(fn ($query) => $query->with('region'))
                        ->getOptionLabelFromRecordUsing(fn (District $record) => $record->name." ({$record->region->name})"),
                    Municipality::class => Type::make(Municipality::class)
                        ->titleAttribute('name')
                        ->label(__('core::types.Municipality'))
                        ->modifyOptionsQueryUsing(fn ($query) => $query->with('district'))
                        ->getOptionLabelFromRecordUsing(fn (Municipality $record) => $record->name." ({$record->district->name})"),
                    MunicipalityPart::class => Type::make(MunicipalityPart::class)
                        ->titleAttribute('name')
                        ->label(__('core::types.MunicipalityPart'))
                        ->modifyOptionsQueryUsing(fn ($query) => $query->with('municipality'))
                        ->getOptionLabelFromRecordUsing(fn (MunicipalityPart $record) => $record->name." ({$record->municipality->name})"),
                    Location::class => Type::make(Location::class)
                        ->titleAttribute('name')
                        ->label(__('core::types.Location'))
                        ->modifyOptionsQueryUsing(fn ($query) => $query->with('parent'))
                        ->getOptionLabelFromRecordUsing(fn (Location $record) => $record->name.($record->parent ? " ({$record->parent->name})" : '')),
                    default => throw new \InvalidArgumentException("Unsupported locality type: {$class}"),
                };
            }

            return $types;
        });
    }

    public function includeLocation(bool|Closure $condition = true): static
    {
        $this->includeLocation = $condition;

        return $this;
    }

    public function shouldIncludeLocation(): bool
    {
        return (bool) $this->evaluate($this->includeLocation);
    }
}
