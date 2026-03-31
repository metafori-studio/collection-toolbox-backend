<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Core\Database\Seeders\RoleSeeder;
use Metafori\Core\Tests\Concerns\InteractsWithStatefulHeaders;
use Metafori\Core\Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Unit');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->use(InteractsWithStatefulHeaders::class)
    ->in('Feature');

pest()->beforeEach(function () {
    $this->seed(RoleSeeder::class);
})->in('Feature/Filament');
