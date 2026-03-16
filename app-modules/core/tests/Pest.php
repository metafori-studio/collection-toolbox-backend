<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Core\Tests\Concerns\InteractsWithStatefulHeaders;

pest()->extend(Metafori\Core\Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->use(InteractsWithStatefulHeaders::class)
    ->in('Feature');
