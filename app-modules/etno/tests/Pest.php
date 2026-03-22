<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Core\Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
