<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Metafori\Core\Database\Seeders\RoleSeeder;
use Metafori\Core\Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->beforeEach(function () {
    Filament::setCurrentPanel('archeo');
    $this->seed(RoleSeeder::class);
})->in('Feature/Filament');