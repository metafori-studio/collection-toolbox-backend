<?php

namespace Metafori\Etno\Support;

use Metafori\Core\Support\Frontend as CoreFrontend;

class Frontend extends CoreFrontend
{
    public function itemUrl(string $id): string
    {
        return (string) $this->uri()
            ->withPath(str_replace('{id}', $id, config('etno.frontend.routes.item_detail')));
    }
}
