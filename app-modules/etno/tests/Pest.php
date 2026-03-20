<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(Metafori\Core\Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
