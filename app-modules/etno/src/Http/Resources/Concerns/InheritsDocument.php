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
        $attribute = $this->resolveInheritableAttribute($key);

        return $this->resource->isInheritableAndInherited($attribute)
            ? $this->getDocumentResource()->{$key}
            : parent::__get($key);
    }

    protected function whenLoaded($relationship, $value = null, $default = new MissingValue)
    {
        $attribute = $this->resolveInheritableAttribute($relationship);

        return $this->resource->isInheritableAndInherited($attribute)
            ? $this->getDocumentResource()->whenLoaded($relationship, $value, $default)
            : parent::whenLoaded($relationship, $value, $default);
    }

    protected function resolveInheritableAttribute(string $attribute)
    {
        if (! method_exists($this->resource, $attribute)) {
            return $attribute;
        }

        $relation = $this->resource->{$attribute}();

        return $relation instanceof BelongsTo && ! $relation instanceof MorphTo
            ? $relation->getForeignKeyName()
            : $attribute;
    }
}
