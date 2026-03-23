<?php

namespace Metafori\Etno\Http\Resources\Concerns;

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

    protected function whenLoaded($relation, $value = null, $default = new MissingValue)
    {
        return $this->isInheritableAndInherited($relation)
            ? $this->getDocumentResource()->whenLoaded($relation, $value, $default)
            : parent::whenLoaded($relation, $value, $default);
    }
}
