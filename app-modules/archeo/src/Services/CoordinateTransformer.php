<?php

namespace Metafori\Archeo\Services;

use proj4php\Point;
use proj4php\Proj;
use proj4php\Proj4php;

/**
 * Service for high-precision coordinate system transformations.
 * Specifically handles S-JTSK (Krovak) to WGS84 conversion using Proj4php.
 */
class CoordinateTransformer
{
    /**
     * Converts S-JTSK (Krovak) coordinates to WGS84 (GCS Lat/Long).
     *
     * @param  float  $x  Negative JTSK coordinate (e.g. -1248141)
     * @param  float  $y  Negative JTSK coordinate (e.g. -424228)
     * @return array{latitude: float, longitude: float}|null
     */
    public function sjtskToWgs84(float $x, float $y): ?array
    {
        // For JTSK, the official input usually expects Y (Easting) then X (Northing).
        // Since we are working with negative coordinates (e.g., -1248141, -424228)
        // standard in some Slovak systems, we use them directly with EPSG:5514.

        $proj4 = new Proj4php;

        // Standard WGS84
        $wgs84 = new Proj('EPSG:4326', $proj4);

        // S-JTSK / Krovak East North (EPSG:5514)
        // with Bursa-Wolf parameters for Slovakia (EPSG:8368)
        $jtskDef = '+proj=krovak +lat_0=49.5 +lon_0=24.83333333333333 +alpha=30.28813972222222 +k=0.9999 +x_0=0 +y_0=0 +ellps=bessel +towgs84=485.021,169.465,483.839,-7.786342,-4.397554,-4.102655,0 +units=m +no_defs';
        $jtsk = new Proj($jtskDef, $proj4);

        try {
            // Proj4 expects Point($x, $y).
            // In EPSG:5514, the typical input format is (Y, X).
            // Example: $y = -424228 (West/East), $x = -1248141 (South/North)
            $pointSrc = new Point($y, $x, $jtsk);
            $pointDest = $proj4->transform($wgs84, $pointSrc);

            return [
                'latitude' => round($pointDest->y, 6),
                'longitude' => round($pointDest->x, 6),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
