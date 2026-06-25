<?php

use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\DocumentOriginator;

it('includes originator label in citation when person is not linked', function () {
    $document = Document::factory()->create([
        'title' => 'Test document',
        'subtitle' => null,
        'publication_date_start' => '2026-03-15',
    ]);

    DocumentOriginator::factory()
        ->for($document)
        ->create([
            'person_id' => null,
            'label' => 'unknown originator',
        ]);

    $citationSk = $document->howToCite;
    expect($citationSk)->toContain('Unknown originator');
});
