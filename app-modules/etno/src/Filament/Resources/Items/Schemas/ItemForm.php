<?php

namespace Metafori\Etno\Filament\Resources\Items\Schemas;

use Filament\Schemas\Schema;
use Metafori\Etno\Filament\Schemas\Components\AdditionalNotesSection;
use Metafori\Etno\Filament\Schemas\Components\AuthorsAndCreatorsSection;
use Metafori\Etno\Filament\Schemas\Components\BasicInformationSection;
use Metafori\Etno\Filament\Schemas\Components\DescriptiveInformationSection;
use Metafori\Etno\Filament\Schemas\Components\GeographicInformationSection;
use Metafori\Etno\Filament\Schemas\Components\ProvenanceAndResearchContextSection;
use Metafori\Etno\Filament\Schemas\Components\RightsAndAccessSection;
use Metafori\Etno\Filament\Schemas\Components\TechnicalAndFormatInformationSection;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                BasicInformationSection::make(),
                DescriptiveInformationSection::make(),
                AuthorsAndCreatorsSection::make(),
                GeographicInformationSection::make(),
                TechnicalAndFormatInformationSection::make(),
                ProvenanceAndResearchContextSection::make(),
                RightsAndAccessSection::make(),
                AdditionalNotesSection::make(),
            ])
            ->columns(1);
    }
}
