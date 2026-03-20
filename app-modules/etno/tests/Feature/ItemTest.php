<?php

use Illuminate\Support\Facades\Cache;
use Metafori\Core\Models\Location;
use Metafori\Etno\Models\Item;

it('invalidates map points cache when item locality is created', function () {
    Cache::set('etno.item.map-points', []);
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    Item::factory()->for($localityWithCoordinates, 'locality')->create();

    expect(Cache::has('etno.item.map-points'))->toBeFalse();
});

it('does not invalidate map points cache when item title is updated', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $item = Item::factory()->for($localityWithCoordinates, 'locality')->create();

    Cache::set('etno.item.map-points', []);
    $item->update(['title' => 'updated']);

    expect(Cache::has('etno.item.map-points'))->toBeTrue();
});

it('invalidates map points cache when item locality is updated', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $item = Item::factory()->for($localityWithCoordinates, 'locality')->create();

    Cache::set('etno.item.map-points', []);
    $newLocalityWithCoordinates = Location::factory()->withCoordinates()->create();
    $item->locality()->associate($newLocalityWithCoordinates)->save();

    expect(Cache::has('etno.item.map-points'))->toBeFalse();
});

it('invalidates map points cache when item with locality is deleted', function () {
    $localityWithCoordinates = Location::factory()->withCoordinates()->create();
    $item = Item::factory()->for($localityWithCoordinates, 'locality')->create();

    Cache::set('etno.item.map-points', []);
    $item->delete();

    expect(Cache::has('etno.item.map-points'))->toBeFalse();
});

it('does not invalidate map points cache when locality name is updated', function () {
    $locality = Location::factory()->withCoordinates()->create();
    Item::factory()->for($locality, 'locality')->create();

    Cache::set('etno.item.map-points', []);
    $locality->update(['name' => 'updated']);

    expect(Cache::has('etno.item.map-points'))->toBeTrue();
});

it('invalidates map points cache when locality coordinates are updated', function () {
    $locality = Location::factory()->withCoordinates()->create();
    Item::factory()->for($locality, 'locality')->create();

    Cache::set('etno.item.map-points', []);
    $locality->update(['latitude' => fake()->latitude()]);

    expect(Cache::has('etno.item.map-points'))->toBeFalse();
});

it('invalidates map points cache when locality is deleted', function () {
    $locality = Location::factory()->withCoordinates()->create();
    Item::factory()->for($locality, 'locality')->create();

    Cache::set('etno.item.map-points', []);
    $locality->delete();

    expect(Cache::has('etno.item.map-points'))->toBeFalse();
});
