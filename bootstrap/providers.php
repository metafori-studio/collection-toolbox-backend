<?php

use App\Providers\AppServiceProvider;
use Metafori\Core\Providers\CoreServiceProvider;
use Metafori\Opensearch\Providers\OpensearchServiceProvider;

return [
    AppServiceProvider::class,
    CoreServiceProvider::class,
    OpensearchServiceProvider::class,
];
