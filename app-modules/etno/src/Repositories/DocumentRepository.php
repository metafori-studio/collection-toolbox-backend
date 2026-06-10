<?php

namespace Metafori\Etno\Repositories;

use Metafori\Etno\Models\Document;

class DocumentRepository
{
    public function findOrFail(string $id): Document
    {
        return Document::query()
            ->published()
            ->with(Document::relations())
            ->findOrFail($id);
    }
}
