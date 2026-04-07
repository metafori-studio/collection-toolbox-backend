<?php

namespace Metafori\Core\Database\Seeders\Locality;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\Region;

class SlovakiaSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::firstOrCreate(
            ['name->sk' => 'Slovensko'],
            ['name' => ['sk' => 'Slovensko']]
        );

        try {
            DB::beginTransaction();

            $jsonPath = __DIR__.'/data/sk.json';
            $jsonContents = file_get_contents($jsonPath);
            if ($jsonContents === false) {
                throw new \RuntimeException("Unable to read {$jsonPath}");
            }

            $jsonData = json_decode($jsonContents, flags: JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY);
            if (! \is_array($jsonData)) {
                throw new \UnexpectedValueException('Expected a top-level array in sk.json.');
            }

            foreach ($jsonData as $regionData) {
                $regionName = $regionData['name'];

                $region = Region::updateOrCreate(
                    [
                        'country_id' => $country->id,
                        'name->sk' => $regionName,
                    ],
                    [
                        'name' => ['sk' => $regionName],
                        'latitude' => $regionData['latitude'],
                        'longitude' => $regionData['longitude'],
                    ]
                );

                foreach ($regionData['districts'] ?? [] as $districtData) {
                    $districtName = $districtData['name'];

                    $district = District::updateOrCreate(
                        [
                            'region_id' => $region->id,
                            'name->sk' => $districtName,
                        ],
                        [
                            'name' => ['sk' => $districtName],
                            'latitude' => $districtData['latitude'],
                            'longitude' => $districtData['longitude'],
                        ]
                    );

                    foreach ($districtData['municipalities'] ?? [] as $municipalityData) {
                        $municipalityName = $municipalityData['name'];

                        Municipality::updateOrCreate(
                            [
                                'district_id' => $district->id,
                                'name->sk' => $municipalityName,
                            ],
                            [
                                'name' => ['sk' => $municipalityName],
                                'latitude' => $municipalityData['latitude'],
                                'longitude' => $municipalityData['longitude'],
                            ]
                        );
                    }
                }
            }

            DB::commit();
            $this->command->info('Localities imported successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('An error occurred during import: '.$e->getMessage());
            throw $e;
        }
    }
}
