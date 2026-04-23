<?php

namespace Metafori\Etno\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;

trait WithDocument
{
    public function getDocument(): Document
    {
        if (
            isset($this->record)
            && ($document = $this->extractDocumentFromRecord($this->record))
        ) {
            return $document;
        }

        if (
            method_exists($this, 'getOwnerRecord')
            && ($record = $this->getOwnerRecord())
            && ($document = $this->extractDocumentFromRecord($record))
        ) {
            return $document;
        }

        if (
            method_exists($this, 'getParentRecord')
            && ($record = $this->getParentRecord())
            && ($document = $this->extractDocumentFromRecord($record))
        ) {
            return $document;
        }

        throw new \LogicException('Unable to resolve Document from record, owner record, or parent record.');
    }

    protected function extractDocumentFromRecord(Model $record): ?Document
    {
        if ($record instanceof Document) {
            return $record;
        }

        if ($record instanceof Item) {
            return $record->document;
        }

        return null;
    }
}
