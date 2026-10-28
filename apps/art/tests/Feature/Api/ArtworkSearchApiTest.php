<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Art\Models\Artwork;
use Metafori\Art\Repositories\ArtworkRepository;
use Metafori\Opensearch\Testing\RefreshIndices;

use function Pest\Laravel\withHeader;

uses(RefreshDatabase::class, RefreshIndices::class);

it('can search artworks using full-text search', function () {
    $artwork1 = Artwork::factory()->create([
        'title' => ['cs' => 'Slunečnice v zahradě', 'en' => 'Sunflowers in the garden'],
    ]);
    $artwork2 = Artwork::factory()->create([
        'title' => ['cs' => 'Noční hlídka', 'en' => 'Night watch'],
    ]);

    app(ArtworkRepository::class)->refreshIndex();

    $response = withHeader('Accept-Language', 'cs')->getJson(route('artworks.search', ['q' => 'slunecnice']));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $artwork1->id);
});
