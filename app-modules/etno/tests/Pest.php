<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Core\Database\Seeders\RoleSeeder;

pest()->extend(Metafori\Core\Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->beforeEach(function () {
    Filament::setCurrentPanel('etno');
    $this->seed(RoleSeeder::class);
})->in('Feature/Filament');

pest()->afterEach(function () {
    fake()->unique(reset: true);
});
