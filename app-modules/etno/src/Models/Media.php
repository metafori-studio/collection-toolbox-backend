<?php

namespace Metafori\Etno\Models;

use Metafori\Etno\Enums\MediaType;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    public function getType(): ?MediaType
    {
        return MediaType::tryFrom($this->collection_name);
    }
}
