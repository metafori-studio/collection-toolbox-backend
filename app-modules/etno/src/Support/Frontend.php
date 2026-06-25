<?php

namespace Metafori\Etno\Support;

use Metafori\Core\Support\Frontend as CoreFrontend;

class Frontend extends CoreFrontend
{
    public static function documentUrl(string $id, ?string $locale = null): string
    {
        return (new self)->route('document', [
            'id' => $id,
            'locale' => $locale ?? app()->getLocale(),
        ]);
    }

    public static function itemUrl(string $id, ?string $locale = null): string
    {
        return (new self)->route('item', [
            'id' => $id,
            'locale' => $locale ?? app()->getLocale(),
        ]);
    }
}
