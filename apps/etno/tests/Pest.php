<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Core\Database\Seeders\RoleSeeder;
use Metafori\Core\Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in(__DIR__.'/Feature');

pest()->beforeEach(function () {
    Filament::setCurrentPanel('admin');
    $this->seed(RoleSeeder::class);
})->in(__DIR__.'/Feature/Filament');

pest()->afterEach(function () {
    fake()->unique(reset: true);
});
