<?php

namespace Metafori\Etno\Http\Resources\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Resources\MissingValue;
use Metafori\Etno\Http\Resources\DocumentResource;

trait InheritsDocument
{
    protected DocumentResource $documentResource;

    protected function getDocumentResource(): DocumentResource
    {
        return $this->documentResource ??= $this->resource->document->toResource();
    }

    public function __get($key): mixed
    {
        return $this->isInheritableAndInherited($key)
            ? $this->getDocumentResource()->{$key}
            : parent::__get($key);
    }

    protected function whenLoaded($relationship, $value = null, $default = new MissingValue)
    {
        $relation = $this->{$relationship}();

        $attribute = $relation instanceof BelongsTo && ! $relation instanceof MorphTo
            ? $relation->getForeignKeyName()
            : $relationship;

        return $this->isInheritableAndInherited($attribute)
            ? $this->getDocumentResource()->whenLoaded($relationship, $value, $default)
            : parent::whenLoaded($relationship, $value, $default);
    }
}
