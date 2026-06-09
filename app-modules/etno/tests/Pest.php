<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Core\Database\Seeders\RoleSeeder;
use Metafori\Core\Tests\TestCase;
use Pest\Livewire\InteractsWithLivewire;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->use(InteractsWithLivewire::class)
    ->in(__DIR__.'/Feature');

pest()->beforeEach(function () {
    Filament::setCurrentPanel('etno');
    $this->seed(RoleSeeder::class);
})->in(__DIR__.'/Feature/Filament');

pest()->afterEach(function () {
    fake()->unique(reset: true);
});
