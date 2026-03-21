<?php

namespace Metafori\Core\Faker\Providers;

use Faker\Provider\Base;

class RorIdProvider extends Base
{
    /**
     * Generate a mathematically valid 9-character ROR ID.
     */
    public function rorId(): string
    {
        $base = static::regexify('0[0-9a-hj-km-np-tv-z]{6}');

        $converted = '';
        foreach (str_split($base) as $char) {
            $converted .= is_numeric($char) ? $char : (ord(strtoupper($char)) - 55);
        }

        $remainder = 0;
        foreach (str_split($converted.'00') as $digit) {
            $remainder = ($remainder * 10 + (int) $digit) % 97;
        }

        $checksum = str_pad((string) (98 - $remainder), 2, '0', STR_PAD_LEFT);

        return $base.$checksum;
    }
}
