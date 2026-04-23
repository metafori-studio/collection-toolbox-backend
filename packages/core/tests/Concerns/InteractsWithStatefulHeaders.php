<?php

namespace Metafori\Core\Tests\Concerns;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

trait InteractsWithStatefulHeaders
{
    protected function setUpInteractsWithStatefulHeaders(): void
    {
        $this->withHeader('referer', 'http://localhost');
        $this->withoutMiddleware(PreventRequestForgery::class);
    }
}
