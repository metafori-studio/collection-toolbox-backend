<?php

namespace Metafori\Core\Database\Seeders\Locality;

use Illuminate\Database\Seeder;
use Metafori\Core\Enums\LocalityType;
use Metafori\Core\Models\Locality;

class SlovakiaSeeder extends Seeder
{
    public function run(): void
    {
        $country = Locality::firstOrCreate(
            ['type' => LocalityType::COUNTRY],
            ['name' => ['sk' => 'Slovensko']]
        );

        $csvPath = __DIR__.'/data/sk.csv';
        $handle = fopen($csvPath, 'r');

        $regionsMap = [];
        $citiesMap = [];
        $boroughsMap = [];

        try {
            while (($data = fgetcsv($handle, separator: ';')) !== false) {
                [$regionName, $cityName, $boroughName] = $data;

                if (! isset($regionsMap[$regionName])) {
                    $region = Locality::firstOrCreate(
                        [
                            'type' => LocalityType::REGION,
                            'parent_id' => $country->id,
                            'name->sk' => $regionName,
                        ],
                        [
                            'name' => ['sk' => $regionName],
                        ]
                    );
                    $regionsMap[$regionName] = $region->id;
                }
                $regionId = $regionsMap[$regionName];

                $cityKey = "$regionId|$cityName";
                if (! isset($citiesMap[$cityKey])) {
                    $city = Locality::firstOrCreate(
                        [
                            'type' => LocalityType::CITY,
                            'parent_id' => $regionId,
                            'name->sk' => $cityName,
                        ],
                        [
                            'name' => ['sk' => $cityName],
                        ]
                    );
                    $citiesMap[$cityKey] = $city->id;
                }
                $cityId = $citiesMap[$cityKey];

                $boroughKey = "$cityId|$boroughName";
                if (! isset($boroughsMap[$boroughKey])) {
                    $borough = Locality::firstOrCreate(
                        [
                            'type' => LocalityType::BOROUGH,
                            'parent_id' => $cityId,
                            'name->sk' => $boroughName,
                        ],
                        [
                            'name' => ['sk' => $boroughName],
                        ]
                    );
                    $boroughsMap[$boroughKey] = $borough->id;
                }
            }
            $this->command->info('Localities imported successfully!');
        } catch (\Exception $e) {
            $this->command->error('An error occurred during import: '.$e->getMessage());
        }

        if (\is_resource($handle)) {
            fclose($handle);
        }
    }
}
