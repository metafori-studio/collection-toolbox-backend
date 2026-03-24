<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\AccessRightsSelect;
use Metafori\Etno\Filament\Forms\Components\LicenseSelect;
use Metafori\Etno\Filament\Forms\Components\TermsOfUseInput;
use Metafori\Etno\Filament\Schemas\Components\Concerns\HasInheritable;

class RightsAndAccessSection extends Section
{
    use HasInheritable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Rights and Access')
            ->schema(fn () => [
                AccessRightsSelect::make('access_rights')
                    ->inheritable($this->inheritable),
                LicenseSelect::make('license')
                    ->inheritable($this->inheritable),
                TermsOfUseInput::make('terms_of_use')
                    ->inheritable($this->inheritable)
                    ->translatableTabs()
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
