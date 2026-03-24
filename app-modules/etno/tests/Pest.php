<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(Metafori\Core\Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->beforeEach(fn () => Filament::setCurrentPanel('etno'))
    ->in('Feature/Filament');
