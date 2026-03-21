<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\AccessRightsSelect;
use Metafori\Etno\Filament\Forms\Components\LicenseSelect;
use Metafori\Etno\Filament\Forms\Components\TermsOfUseInput;

class RightsAndAccessSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Rights and Access')
            ->schema([
                AccessRightsSelect::make('access_rights'),
                LicenseSelect::make('license'),
                TermsOfUseInput::make('terms_of_use')
                    ->translatableTabs()
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
