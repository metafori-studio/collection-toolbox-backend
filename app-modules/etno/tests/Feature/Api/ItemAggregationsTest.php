<?php

use Metafori\Core\Models\Organization;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Repositories\ItemRepository;
use Metafori\Opensearch\Testing\RefreshIndices;

use function Pest\Laravel\getJson;

uses(RefreshIndices::class);

it('can get aggregations for items', function () {
    Item::factory()->create([
        'type' => ItemType::AudioRecording,
        'document_overrides' => ['type'],
    ]);

    Item::factory()->count(2)->create([
        'type' => ItemType::Map,
        'document_overrides' => ['type'],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.aggregations'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'type' => [
                    '*' => ['value', 'label', 'count'],
                ],
            ],
        ]);

    $data = $response->json('data.type');

    $mapAgg = collect($data)->firstWhere('value', ItemType::Map->value);
    $audioAgg = collect($data)->firstWhere('value', ItemType::AudioRecording->value);

    expect($mapAgg['count'])->toBe(2)
        ->and($audioAgg['count'])->toBe(1);
});

it('can filter aggregations by another property', function () {
    $org1 = Organization::factory()->create();
    $org2 = Organization::factory()->create();

    Item::factory()->create([
        'type' => ItemType::AudioRecording,
        'institution_id' => $org1->id,
        'document_overrides' => ['type', 'institution'],
    ]);

    Item::factory()->create([
        'type' => ItemType::Map,
        'institution_id' => $org2->id,
        'document_overrides' => ['type', 'institution'],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.aggregations', [
        'filter' => ['institution.id' => [$org1->id]],
    ]));

    $response->assertStatus(200);

    $typeData = $response->json('data.type');

    expect(collect($typeData)->firstWhere('value', ItemType::AudioRecording->value)['count'])->toBe(1)
        ->and(collect($typeData)->firstWhere('value', ItemType::Map->value))->toBeNull();
});

it('ignores filters for the same field when aggregating', function () {
    $org1 = Organization::factory()->create();
    $org2 = Organization::factory()->create();

    Item::factory()->create([
        'type' => ItemType::AudioRecording,
        'institution_id' => $org1->id,
        'document_overrides' => ['type', 'institution'],
    ]);

    Item::factory()->create([
        'type' => ItemType::Map,
        'institution_id' => $org2->id,
        'document_overrides' => ['type', 'institution'],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.aggregations', [
        'filter' => ['type' => [ItemType::AudioRecording->value]],
    ]));

    $response->assertStatus(200);

    $typeData = $response->json('data.type');

    expect(collect($typeData)->firstWhere('value', ItemType::AudioRecording->value)['count'])->toBe(1)
        ->and(collect($typeData)->firstWhere('value', ItemType::Map->value)['count'])->toBe(1);

    $data = $response->json('data');
    $instData = $data['institution.id'] ?? [];

    expect(collect($instData)->firstWhere('value', $org1->id)['count'])->toBe(1)
        ->and(collect($instData)->firstWhere('value', $org2->id))->toBeNull();
});

it('resolves model labels for aggregations', function () {
    $org = Organization::factory()->create(['name' => 'Test Organization']);

    Item::factory()->create([
        'institution_id' => $org->id,
        'document_overrides' => ['institution'],
    ]);

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.aggregations'));

    $response->assertStatus(200);

    $data = $response->json('data');
    $instData = $data['institution.id'] ?? [];

    $orgAgg = collect($instData)->firstWhere('value', $org->id);

    expect($orgAgg['label'])->toBe('Test Organization')
        ->and($orgAgg['count'])->toBe(1);
});
