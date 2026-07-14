<?php

use Metafori\Core\Enums\Language;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\User;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
use Metafori\Opensearch\Testing\RefreshIndices;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshIndices::class);

it('can show a complete item with all relations', function () {
    $document = Document::factory()
        ->state(['access_rights' => AccessRights::OpenAccess])
        ->hasAuthors(2)
        ->hasResearchers(2)
        ->hasKeywords(2)
        ->hasResearchCollections(2)
        ->hasOriginators(2)
        ->for(MunicipalityPart::factory(), 'locality');
    $item = Item::factory()
        ->withTranscribedMedia()
        ->for($document, 'document')
        ->create();

    $response = getJson(route('api.etno.items.show', $item->identifier));

    $document = $item->document
        ->load('originators.person');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data.authors')
        ->assertJsonCount(2, 'data.researchers')
        ->assertJsonCount(2, 'data.originators')
        ->assertJsonCount(2, 'data.keywords')
        ->assertJsonCount(2, 'data.research_collections')
        ->assertJsonStructure([
            'data' => [
                'id',
                'document_id',
                'doi',
                'title',
                'subtitle',
                'abstract',
                'general_note',
                'terms_of_use',
                'location_note',
                'content_note',
                'technical_note',
                'type',
                'media' => [
                    'documents' => [
                        '*' => [
                            'conversions',
                            'file_name',
                            'human_readable_size',
                            'id',
                            'mime_type',
                            'name',
                            'transcript',
                            'url',
                        ],
                    ],
                ],
                'languages',
                'accrual_method',
                'collection_method',
                'access_rights',
                'license',
                'production_methods',
                'time_period_start',
                'time_period_end',
                'time_period_settings',
                'submission_date_start',
                'submission_date_end',
                'submission_date_settings',
                'publication_date_start',
                'publication_date_end',
                'publication_date_settings',
                'how_to_cite',
                'institution' => [
                    'id',
                    'name',
                    'ror_id',
                ],
                'project' => [
                    'id',
                    'title',
                ],
                'locality' => [
                    'id',
                    'name',
                    'municipality' => [
                        'id',
                        'name',
                        'district' => [
                            'id',
                            'name',
                            'region' => [
                                'id',
                                'name',
                                'country' => [
                                    'id',
                                    'name',
                                ],
                            ],
                        ],
                    ],
                ],
                'authors' => [
                    '*' => [
                        'id',
                        'given_name',
                        'family_name',
                        'display_name',
                        'orcid',
                    ],
                ],
                'researchers' => [
                    '*' => [
                        'id',
                        'given_name',
                        'family_name',
                        'display_name',
                        'orcid',
                    ],
                ],
                'originators' => [
                    '*' => [
                        'id',
                        'label',
                        'person' => [
                            'id',
                            'given_name',
                            'family_name',
                            'display_name',
                            'orcid',
                        ],
                    ],
                ],
                'keywords' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
                'research_collections' => [
                    '*' => [
                        'id',
                        'title',
                    ],
                ],
                'extents' => [
                    '*' => [
                        'value',
                        'unit',
                    ],
                ],
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $item->identifier,
                'document_id' => $document->id,
                'doi' => $document->doi,
                'title' => $document->title,
                'subtitle' => $document->subtitle,
                'abstract' => $document->abstract,
                'general_note' => $document->general_note,
                'terms_of_use' => $document->terms_of_use,
                'location_note' => $document->location_note,
                'content_note' => $document->content_note,
                'technical_note' => $document->technical_note,
                'how_to_cite' => $document->how_to_cite,
                'type' => $document->type?->value,
                'extents' => collect($document->extents)->toArray(),
                'languages' => collect($document->languages)
                    ->map(fn (Language $lang) => $lang->value)
                    ->toArray(),
                'accrual_method' => $document->accrual_method?->value,
                'collection_method' => $document->collection_method?->value,
                'access_rights' => $document->access_rights?->value,
                'license' => $document->license?->value,
                'production_methods' => collect($document->production_methods)
                    ->map(fn (ProductionMethod $method) => $method->value)
                    ->toArray(),
                'time_period_start' => $document->time_period_start?->toJSON(),
                'time_period_end' => $document->time_period_end?->toJSON(),
                'time_period_settings' => $document->time_period_settings
                    ? collect($document->time_period_settings)->toArray()
                    : null,
                'submission_date_start' => $document->submission_date_start?->toJSON(),
                'submission_date_end' => $document->submission_date_end?->toJSON(),
                'submission_date_settings' => $document->submission_date_settings
                    ? collect($document->submission_date_settings)->toArray()
                    : null,
                'publication_date_start' => $document->publication_date_start?->toJSON(),
                'publication_date_end' => $document->publication_date_end?->toJSON(),
                'publication_date_settings' => $document->publication_date_settings
                    ? collect($document->publication_date_settings)->toArray()
                    : null,
                'institution' => [
                    'id' => $document->institution->id,
                    'name' => $document->institution->name,
                    'ror_id' => $document->institution->ror_id,
                ],
                'project' => [
                    'id' => $document->project->id,
                    'title' => $document->project->title,
                ],
                'locality' => [
                    'id' => $document->locality->id,
                    'name' => $document->locality->name,
                ],
                'authors' => $document->authors->map(fn ($author) => [
                    'id' => $author->id,
                    'given_name' => $author->given_name,
                    'family_name' => $author->family_name,
                    'display_name' => $author->display_name,
                    'orcid' => $author->orcid,
                ])->toArray(),
                'researchers' => $document->researchers->map(fn ($researcher) => [
                    'id' => $researcher->id,
                    'given_name' => $researcher->given_name,
                    'family_name' => $researcher->family_name,
                    'display_name' => $researcher->display_name,
                    'orcid' => $researcher->orcid,
                ])->toArray(),
                'originators' => $document->originators->map(fn ($originator) => [
                    'id' => $originator->id,
                    'label' => $originator->label,
                    'person' => [
                        'id' => $originator->person->id,
                        'given_name' => $originator->person->given_name,
                        'family_name' => $originator->person->family_name,
                        'display_name' => $originator->person->display_name,
                        'orcid' => $originator->person->orcid,
                    ],
                ])->toArray(),
                'keywords' => $document->keywords->map(fn ($keyword) => [
                    'id' => $keyword->id,
                    'name' => $keyword->name,
                ])->toArray(),
                'research_collections' => $document->researchCollections->map(fn ($collection) => [
                    'id' => $collection->id,
                    'title' => $collection->title,
                ])->toArray(),
            ],
        ]);
});

it('returns 404 for non-existent item', function () {
    $response = getJson(route('api.etno.items.show', 'invalid-id'));

    $response->assertStatus(404);
});

it('shows media when access rights are open access', function () {
    $item = Item::factory()->withTranscribedMedia()->create();
    $item->document->update(['access_rights' => AccessRights::OpenAccess]);

    $response = getJson(route('api.etno.items.show', $item->identifier));

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['media' => [
            'documents' => [['name', 'file_name', 'url', 'transcript']],
        ]]])
        ->assertJsonCount(1, 'data.media.documents');
});

it('does not show media when access rights are restricted and user is not authenticated', function () {
    $item = Item::factory()->withTranscribedMedia()->create();
    $item->document->update(['access_rights' => AccessRights::RestrictedAccess]);

    $response = getJson(route('api.etno.items.show', $item->identifier));

    $response->assertStatus(200);
    expect($response->json('data'))->not->toHaveKey('media');
});

it('shows media when access rights are restricted and user is authenticated', function () {
    $item = Item::factory()->withTranscribedMedia()->create();
    $item->document->update(['access_rights' => AccessRights::RestrictedAccess]);

    $user = User::factory()->create();

    $response = actingAs($user)->getJson(route('api.etno.items.show', $item->identifier));

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['media' => [
            'documents' => [['name', 'file_name', 'url', 'transcript']],
        ]]])
        ->assertJsonCount(1, 'data.media.documents');
});

it('does not show media when access rights are embargoed and user is authenticated', function () {
    $item = Item::factory()->withTranscribedMedia()->create();
    $item->document->update(['access_rights' => AccessRights::EmbargoedAccess]);

    $user = User::factory()->create();

    $response = actingAs($user)->getJson(route('api.etno.items.show', $item->identifier));

    $response->assertStatus(200);
    expect($response->json('data'))->not->toHaveKey('media');
});

it('returns 404 for unpublished items', function () {
    $item = Item::factory()
        ->for(Document::factory()->published(false))
        ->create();

    $response = getJson(route('api.etno.items.show', $item->identifier));

    $response->assertStatus(404);
});
