<?php

use function Pest\Laravel\getJson;

it('can list translations', function () {
    $response = getJson(route('api.etno.translations.index'));

    $enumKeys = [
        'AccessRights',
        'AccrualMethod',
        'CollectionMethod',
        'ExtentUnit',
        'ItemType',
        'Language',
        'License',
        'ProductionMethod',
    ];

    $response->assertStatus(200)
        ->assertJsonStructure([
            'enums' => $enumKeys,
        ]);

    foreach ($enumKeys as $key) {
        expect($response->json("enums.{$key}"))->toBeArray()->not->toBeEmpty();
    }
});
