<?php

namespace Metafori\Core\Enums\Concerns;

use Illuminate\Support\Str;

trait HasTranslatedLabel
{
    public function getLabel(): ?string
    {
        $className = class_basename($this);
        $module = Str::lower(explode('\\', static::class)[1]);

        return trans("{$module}::enums.{$className}.{$this->value}");
    }
}
