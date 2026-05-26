<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Core\Database\Seeders\RoleSeeder;
use Metafori\Core\Tests\Concerns\InteractsWithStatefulHeaders;
use Metafori\Core\Tests\TestCase;
use Pest\Livewire\InteractsWithLivewire;

pest()->extend(TestCase::class)
    ->in(__DIR__.'/Unit');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->use(InteractsWithLivewire::class)
    ->use(InteractsWithStatefulHeaders::class)
    ->in(__DIR__.'/Feature');

pest()->beforeEach(function () {
    $this->seed(RoleSeeder::class);
})->in(__DIR__.'/Feature/Filament');
