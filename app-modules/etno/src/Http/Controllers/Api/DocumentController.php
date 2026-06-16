<?php

namespace Metafori\Etno\Http\Controllers\Api;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Metafori\Etno\Http\Resources\DocumentResource;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Repositories\DocumentRepository;

class DocumentController
{
    public function __construct(
        private readonly DocumentRepository $repository,
    ) {}

    /**
     * @throws ModelNotFoundException<Document>
     */
    public function show(string $id): DocumentResource
    {
        $document = $this->repository->findOrFail($id);

        return new DocumentResource($document);
    }
}
