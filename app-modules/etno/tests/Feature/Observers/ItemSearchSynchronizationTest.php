<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Metafori\Core\Models\Keyword;
use Metafori\Core\Models\Person;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\DocumentOriginator;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Models\ItemOriginator;
use Metafori\Etno\Models\ResearchCollection;
use Metafori\Etno\Repositories\ItemRepository;
use Metafori\Opensearch\Testing\RefreshIndices;

uses(RefreshIndices::class);

$relations = [
    'authors' => ['authors', 'author.person_id', Person::class],
    'researchers' => ['researchers', 'researcher.person_id', Person::class],
    'keywords' => ['keywords', 'keyword.id', Keyword::class],
    'research collections' => ['researchCollections', 'research_collection.id', ResearchCollection::class],
];

it('removes detached item relations from the search index', function (string $relation, string $field, string $model) {
    config(['scout.queue' => false]);
    $document = Document::factory()->published()->create();
    $item = Item::factory()->for($document, 'document')->create(['document_overrides' => [$relation]]);
    $related = $model::factory()->create();
    $item->{$relation}()->attach($related);
    app(ItemRepository::class)->refreshIndex();
    expect(Item::search()->where($field, $related->id)->raw()['hits']['total']['value'])->toBe(1);

    $item->{$relation}()->detach($related);
    app(ItemRepository::class)->refreshIndex();
    expect(Item::search()->where($field, $related->id)->raw()['hits']['total']['value'])->toBe(0);
})->with($relations);

it('indexes changes to document relations inherited by items', function (string $relation, string $field, string $model) {
    config(['scout.queue' => false]);
    $document = Document::factory()->published()->create();
    Item::factory()->for($document, 'document')->create(['document_overrides' => []]);
    $related = $model::factory()->create();
    $document->{$relation}()->attach($related);
    app(ItemRepository::class)->refreshIndex();
    expect(Item::search()->where($field, $related->id)->raw()['hits']['total']['value'])->toBe(1);

    $document->{$relation}()->detach($related);
    app(ItemRepository::class)->refreshIndex();
    expect(Item::search()->where($field, $related->id)->raw()['hits']['total']['value'])->toBe(0);
})->with($relations);

it('updates indexed transcripts when item media changes', function () {
    config(['scout.queue' => false]);
    Storage::fake('local');
    $document = Document::factory()->published()->create();
    $item = Item::factory()->for($document, 'document')->create();
    $media = $item->addMedia(UploadedFile::fake()->createWithContent('record.pdf', '%PDF-1.4'))
        ->withCustomProperties(['transcripts' => ['txt' => 'uniquetranscript']])
        ->toMediaCollection('document', 'local');
    app(ItemRepository::class)->refreshIndex();
    expect(Item::search('uniquetranscript')->raw()['hits']['total']['value'])->toBe(1);

    $media->setCustomProperty('transcripts.txt', 'replacementtranscript')->save();
    app(ItemRepository::class)->refreshIndex();
    expect(Item::search('uniquetranscript')->raw()['hits']['total']['value'])->toBe(0)
        ->and(Item::search('replacementtranscript')->raw()['hits']['total']['value'])->toBe(1);

    $media->delete();
    app(ItemRepository::class)->refreshIndex();
    expect(Item::search('replacementtranscript')->raw()['hits']['total']['value'])->toBe(0);
});

it('reindexes all removed item relations when detaching without ids', function (string $relation, string $field, string $model) {
    config(['scout.queue' => false]);
    $document = Document::factory()->published()->create();
    $item = Item::factory()->for($document, 'document')->create(['document_overrides' => [$relation]]);
    $related = $model::factory()->count(2)->create();
    $item->{$relation}()->attach($related);
    app(ItemRepository::class)->refreshIndex();
    expect(Item::search()->where($field, $related->first()->id)->raw()['hits']['total']['value'])->toBe(1);

    $item->{$relation}()->detach();
    app(ItemRepository::class)->refreshIndex();
    expect(Item::search()->where($field, $related->first()->id)->raw()['hits']['total']['value'])->toBe(0)
        ->and(Item::search()->where($field, $related->last()->id)->raw()['hits']['total']['value'])->toBe(0);
})->with($relations);

it('preserves item overrides when document relations change', function (string $relation, string $field, string $model) {
    config(['scout.queue' => false]);
    $document = Document::factory()->published()->create();
    $item = Item::factory()->for($document, 'document')->create(['document_overrides' => [$relation]]);
    $own = $model::factory()->create();
    $inherited = $model::factory()->create();
    $item->{$relation}()->attach($own);
    $document->{$relation}()->attach($inherited);
    app(ItemRepository::class)->refreshIndex();
    expect(Item::search()->where($field, $own->id)->raw()['hits']['total']['value'])->toBe(1)
        ->and(Item::search()->where($field, $inherited->id)->raw()['hits']['total']['value'])->toBe(0);
})->with($relations);

it('removes a deleted item originator from the search index', function () {
    config(['scout.queue' => false]);
    $document = Document::factory()->published()->create();
    $item = Item::factory()->for($document, 'document')->create(['document_overrides' => ['originators']]);
    $person = Person::factory()->create();
    $originator = ItemOriginator::factory()->for($item)->for($person)->create();

    app(ItemRepository::class)->refreshIndex();
    expect(Item::search()->where('originator.person_id', $person->id)->raw()['hits']['total']['value'])->toBe(1);

    $originator->delete();
    app(ItemRepository::class)->refreshIndex();

    expect(Item::search()->where('originator.person_id', $person->id)->raw()['hits']['total']['value'])->toBe(0);
});

it('indexes a newly added document originator for inheriting items', function () {
    config(['scout.queue' => false]);
    $document = Document::factory()->published()->create();
    Item::factory()->for($document, 'document')->create(['document_overrides' => []]);
    $person = Person::factory()->create();

    DocumentOriginator::factory()->for($document)->for($person)->create();
    app(ItemRepository::class)->refreshIndex();

    expect(Item::search()->where('originator.person_id', $person->id)->raw()['hits']['total']['value'])->toBe(1);
});

it('removes a deleted document originator from inheriting items in the search index', function () {
    config(['scout.queue' => false]);
    $document = Document::factory()->published()->create();
    $person = Person::factory()->create();
    $originator = DocumentOriginator::factory()->for($document)->for($person)->create();
    Item::factory()->for($document, 'document')->create(['document_overrides' => []]);

    app(ItemRepository::class)->refreshIndex();
    expect(Item::search()->where('originator.person_id', $person->id)->raw()['hits']['total']['value'])->toBe(1);

    $originator->delete();
    app(ItemRepository::class)->refreshIndex();

    expect(Item::search()->where('originator.person_id', $person->id)->raw()['hits']['total']['value'])->toBe(0);
});
