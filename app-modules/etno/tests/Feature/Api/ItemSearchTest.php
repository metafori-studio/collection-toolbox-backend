<?php

use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Repositories\ItemRepository;
use Metafori\Opensearch\Testing\RefreshIndices;

use function Pest\Laravel\getJson;

uses(RefreshIndices::class);

it('can search in slovak title field using stemming', function () {
    $document1 = Document::factory()->published()->create(['title' => ['sk' => 'Ľudové staviteľstvá']]);
    $document2 = Document::factory()->published()->create(['title' => ['sk' => 'Spracovanie ľanu']]);

    $item1 = Item::factory()->for($document1)->create();
    $item2 = Item::factory()->for($document2)->create();

    $item1->searchable();
    $item2->searchable();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.search', ['q' => 'stavitelstvo']), [
        'Accept-Language' => 'sk',
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $item1->identifier);
});

it('can search in slovak title field using prefix', function () {
    $document1 = Document::factory()->published()->create(['title' => ['sk' => 'Tradičná keramika']]);
    $document2 = Document::factory()->published()->create(['title' => ['sk' => 'Spracovanie ľanu']]);

    $item1 = Item::factory()->for($document1)->create();
    $item2 = Item::factory()->for($document2)->create();

    $item1->searchable();
    $item2->searchable();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.search', ['q' => 'keram']), [
        'Accept-Language' => 'sk',
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $item1->identifier);
});

it('can search in english title field using stemming', function () {
    $document1 = Document::factory()->published()->create(['title' => ['en' => 'Folk Architecture']]);
    $document2 = Document::factory()->published()->create(['title' => ['en' => 'Flax Processing']]);

    $item1 = Item::factory()->for($document1)->create();
    $item2 = Item::factory()->for($document2)->create();

    $item1->searchable();
    $item2->searchable();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.search', ['q' => 'architectures']), [
        'Accept-Language' => 'en',
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $item1->identifier);
});

it('can search in english title field using prefix', function () {
    $document1 = Document::factory()->published()->create(['title' => ['en' => 'Running shoes']]);
    $document2 = Document::factory()->published()->create(['title' => ['en' => 'Flax Processing']]);

    $item1 = Item::factory()->for($document1)->create();
    $item2 = Item::factory()->for($document2)->create();

    $item1->searchable();
    $item2->searchable();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.search', ['q' => 'runni']), [
        'Accept-Language' => 'en',
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $item1->identifier);
});

it('can search items by subtitle', function () {
    $document1 = Document::factory()->published()->create(['subtitle' => ['en' => 'The Secret of Wood']]);
    $document2 = Document::factory()->published()->create(['subtitle' => ['en' => 'Ceramics Around Us']]);

    $item1 = Item::factory()->for($document1)->create();
    $item2 = Item::factory()->for($document2)->create();

    $item1->searchable();
    $item2->searchable();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.search', ['q' => 'secret']));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $item1->identifier);
});

it('can search items by abstract', function () {
    $document1 = Document::factory()->published()->create(['abstract' => ['en' => 'This document discusses the history of weaving.']]);
    $document2 = Document::factory()->published()->create(['abstract' => ['en' => 'An analysis of modern painting.']]);

    $item1 = Item::factory()->for($document1)->create();
    $item2 = Item::factory()->for($document2)->create();

    $item1->searchable();
    $item2->searchable();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.search', ['q' => 'weaving']));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $item1->identifier);
});

it('can search items by transcripts', function () {
    $document1 = Document::factory()->published()->create();
    $document2 = Document::factory()->published()->create();

    $item1 = Item::factory()
        ->for($document1)
        ->withTranscribedMedia(txt: 'Recording of a folk song sung by a shepherd.')
        ->create();
    $item2 = Item::factory()
        ->for($document2)
        ->withTranscribedMedia(txt: 'Completely different textual content with no matching words.')
        ->create();

    $item1->searchable();
    $item2->searchable();

    app(ItemRepository::class)->refreshIndex();

    $response = getJson(route('api.etno.items.search', ['q' => 'shepherd']));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $item1->identifier);
});
