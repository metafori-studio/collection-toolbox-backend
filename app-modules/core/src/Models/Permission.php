<?php

namespace Metafori\Core\Models;

use BackedEnum;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Permission\Models\Permission as BasePermission;

/**
 * @property-read (BackedEnum&HasLabel)|null $enum
 * @property-read string $label
 */
class Permission extends BasePermission
{
    /**
     * @return class-string<BackedEnum&HasLabel>|null
     */
    public static function enumClass(): ?string
    {
        return config('permission.enums.permission');
    }

    public function enum(): Attribute
    {
        return Attribute::get(function (): (BackedEnum&HasLabel)|null {
            $enumClass = static::enumClass();

            return $enumClass !== null ? $enumClass::tryFrom($this->name) : null;
        });
    }

    public function label(): Attribute
    {
        return Attribute::get(
            fn (): string => (string) ($this->enum?->getLabel() ?? $this->name)
        );
    }
}
