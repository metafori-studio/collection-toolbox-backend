<?php

use Metafori\Archeo\Services\CoordinateTransformer;

// Official ÚGKK SR / GKÚ Bratislava geodetic reference points (S-JTSK [JTSK03] -> ETRS89/WGS84)
it('transforms official Slovak geodetic reference points from S-JTSK to WGS84', function (float $x, float $y, float $expectedLat, float $expectedLon) {
    $transformer = new CoordinateTransformer;

    $result = $transformer->sjtskToWgs84($x, $y);

    expect($result)
        ->toBeArray()
        ->toHaveKeys(['latitude', 'longitude'])
        ->and($result['latitude'])->toEqualWithDelta($expectedLat, 0.00001)
        ->and($result['longitude'])->toEqualWithDelta($expectedLon, 0.00001);
})->with([
    'Gerlachovský štít (highest peak)' => [-1185882.50, -342337.50, 49.164033, 20.134036],
    'Oravská Polhora (northernmost point)' => [-1132697.0, -388708.0, 49.61380, 19.44851],
    'Patince (southernmost point)' => [-1334760.0, -490048.0, 47.73119, 18.29012],
    'Záhorská Ves (westernmost point)' => [-1252576.0, -591310.0, 48.38053, 16.83320],
    'Nová Sedlica / Kremenec (easternmost point)' => [-1202390.0, -165437.0, 49.08799, 22.56570],
]);

it('maps x to northing and y to easting', function () {
    $transformer = new CoordinateTransformer;

    $base = $transformer->sjtskToWgs84(-1250000.0, -450000.0);
    $north = $transformer->sjtskToWgs84(-1200000.0, -450000.0);
    $east = $transformer->sjtskToWgs84(-1250000.0, -400000.0);

    expect($north['latitude'])->toBeGreaterThan($base['latitude'])
        ->and($east['longitude'])->toBeGreaterThan($base['longitude']);
});
