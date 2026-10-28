<?php

namespace Metafori\Art\Observers;

use Metafori\Art\Models\ArtworkAuthor;

class ArtworkAuthorObserver
{
    public function saved(ArtworkAuthor $author): void
    {
        $author->artwork?->searchable();
    }

    public function deleted(ArtworkAuthor $author): void
    {
        $author->artwork?->searchable();
    }
}
