<?php

namespace Metafori\Core\Tests\Concerns;

trait InteractsWithStatefulHeaders
{
    protected function setUpInteractsWithStatefulHeaders(): void
    {
        $this->withHeader('referer', 'http://localhost');
    }
}
