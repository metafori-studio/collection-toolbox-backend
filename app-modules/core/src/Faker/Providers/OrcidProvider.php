<?php

namespace Metafori\Core\Faker\Providers;

use Faker\Provider\Base;

class OrcidProvider extends Base
{
    /**
     * Generate a mathematically valid 16-character ORCID iD.
     */
    public function orcid(): string
    {
        $base = static::numerify('0000-000#-####-###');
        $digits = str_split(str_replace('-', '', $base));

        $total = 0;
        foreach ($digits as $digit) {
            $total = ($total + (int) $digit) * 2;
        }

        $remainder = $total % 11;
        $result = (12 - $remainder) % 11;

        $checkDigit = $result === 10 ? 'X' : (string) $result;

        return $base.$checkDigit;
    }
}
