<?php

namespace Metafori\Etno\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Metafori\Core\Models\Keyword;
use Metafori\Core\Models\Person;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Models\ResearchCollection;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Increase memory limit for large data generation
        ini_set('memory_limit', '512M');

        $this->command->info('Creating related entities pool...');

        // To make the seeded data realistic and avoid creating too many redundant related rows,
        // we'll create pools of reusable related records.
        $people = Person::factory()->count(200)->create();
        $keywords = Keyword::factory()->count(100)->create();
        $collections = ResearchCollection::factory()->count(20)->create();
        $municipalities = \Metafori\Core\Models\Municipality::factory()->count(30)->create();
        $locations = \Metafori\Core\Models\Location::factory()->count(30)->create();
        $regions = \Metafori\Core\Models\Region::factory()->count(10)->create();

        $localities = $municipalities->concat($locations)->concat($regions);

        $chunkSize = 100;
        $totalItems = 1000;
        $chunks = $totalItems / $chunkSize;

        $this->command->info("Creating {$totalItems} items with related entities in chunks of {$chunkSize}...");

        for ($i = 0; $i < $chunks; $i++) {
            DB::transaction(function () use ($people, $keywords, $collections, $localities, $chunkSize) {
                for ($j = 0; $j < $chunkSize; $j++) {
                    $itemLocality = $localities->random();
                    $docLocality = $localities->random();

                    // 1. Create the Document and assign all pool relations to the document
                    $document = Document::factory()->create([
                        'locality_id' => $docLocality->id,
                        'locality_type' => $docLocality->getMorphClass(),
                    ]);

                    // Attach authors to Document
                    $authors = $people->random(rand(1, 3))->values();
                    $document->authors()->attach(
                        $authors->pluck('id')->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index + 1]])->toArray()
                    );

                    // Attach researchers to Document
                    $researcherCount = rand(0, 2);
                    if ($researcherCount > 0) {
                        $researchers = $people->random($researcherCount)->values();
                        $document->researchers()->attach(
                            $researchers->pluck('id')->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index + 1]])->toArray()
                        );
                    }

                    // Attach originators to Document
                    $originatorCount = rand(1, 2);
                    for ($k = 0; $k < $originatorCount; $k++) {
                        \Metafori\Etno\Models\DocumentOriginator::factory()->create([
                            'document_id' => $document->id,
                            'person_id' => $people->random()->id,
                            'sort_order' => $k + 1,
                        ]);
                    }

                    // Attach keywords to Document
                    $docKeywords = $keywords->random(rand(2, 5))->values();
                    $document->keywords()->attach(
                        $docKeywords->pluck('id')->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index + 1]])->toArray()
                    );

                    // Attach collections to Document
                    if (rand(0, 1) === 1) {
                        $docCollections = $collections->random(1)->values();
                        $document->researchCollections()->attach(
                            $docCollections->pluck('id')->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index + 1]])->toArray()
                        );
                    }

                    // 2. Create the Item with state withDocumentOverrides, per user instruction
                    // Some items can be created with overrides, others default to inheriting from Document
                    $factory = Item::factory();

                    if (rand(0, 1) === 1) {
                        $factory = $factory->withDocumentOverrides();

                        // Because this item overrides the document, we optionally assign it some of its own relations
                        // (User said "do not assign to item", so we keep it minimal or don't assign relations,
                        // giving us items that inherit nothing and override with empty relationships)
                    }

                    $factory->create([
                        'document_id' => $document->id,
                        'locality_id' => $itemLocality->id,
                        'locality_type' => $itemLocality->getMorphClass(),
                    ]);
                }
            });

            $this->command->info('Processed chunk '.($i + 1)." of {$chunks}...");
        }

        $this->command->info("Successfully seeded {$totalItems} items. Don't forget to run indexing if needed.");
    }
}
