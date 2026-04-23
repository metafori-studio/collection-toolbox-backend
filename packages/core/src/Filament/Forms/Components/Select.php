<?php

namespace Metafori\Core\Filament\Forms\Components;

use Filament\Forms\Components\Select as BaseSelect;
use Metafori\Core\Filament\Forms\Components\Concerns\HasSortedOptions;
use Metafori\Core\Filament\Forms\Components\Concerns\SavesOrder;

class Select extends BaseSelect
{
    use HasSortedOptions, SavesOrder;
}
