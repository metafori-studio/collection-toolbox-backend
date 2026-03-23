<?php

namespace Metafori\Etno\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Metafori\Etno\Models\Extent;

class ExtentCollectionCast implements CastsAttributes
{
    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return collect();
        }

        $decoded = json_decode($value, true);

        if (! \is_array($decoded)) {
            return collect();
        }

        return collect($decoded)->map(fn (array $item) => new Extent(
            value: $item['value'],
            unit: $item['unit'],
        ));
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return '[]';
        }

        return collect($value)->toJson();
    }
}
