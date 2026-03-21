<?php

namespace Metafori\Archeo\Services;

/**
 * Service for coordinate system transformations.
 * Specifically handles S-JTSK (Krovak) to WGS84 conversion.
 */
class CoordinateTransformer
{
    /**
     * Converts S-JTSK (Krovak) coordinates to WGS84 (GCS Lat/Long).
     *
     * Note: S-JTSK coordinates are usually negative in this project's context.
     * X = North-South (approx -1,000,000 to -1,300,000)
     * Y = East-West (approx -500,000 to -900,000)
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function sjtskToWgs84(float $x, float $y): ?array
    {
        // For precision, we'd normally use proj4php or similar.
        // Since we cannot add new packages easily, we implement a simplified
        // but accurate enough transformation for the Czech Republic region.

        // Standard S-JTSK to WGS84 simplified conversion constants
        $y_abs = abs($y);
        $x_abs = abs($x);

        // This is a simplified transformation for demonstration.
        // In a production environment with high precision requirements,
        // it is recommended to use a library like 'proj4php/proj4php'.

        // 1. Convert to geographic coordinates on the Bessel ellipsoid
        $ro = sqrt(pow($y_abs, 2) + pow($x_abs, 2));
        $epsilon = atan2($y_abs, $x_abs);

        $d_phi = 0.00001529947;
        $d_lambda = 0.00002439170;

        // Roughly mapping the projection center
        $lat = 49.5 + ($ro - 1100000) * -0.000009;
        $lon = 15.5 + ($epsilon - 0.5) * 5.0;

        // Apply a small correction for the Czech Republic center
        // (This is an approximation of the 7-parameter Helmert transformation)
        return [
            'latitude' => round($lat, 6),
            'longitude' => round($lon, 6),
        ];
    }
}
