<?php

namespace Metafori\Etno\Filament\Contracts;

use Metafori\Etno\Models\Document;

interface HasDocument
{
    public function getDocument(): Document;
}
