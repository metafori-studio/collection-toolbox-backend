<?php

namespace Metafori\Etno\Filament\Schemas;

use Filament\Schemas\Schema;

class SharedMetadataSchema
{
    public static function apply(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\BasicInformationSection::make(),
                Components\DescriptiveInformationSection::make(),
                Components\AuthorsAndCreatorsSection::make(),
                Components\GeographicInformationSection::make(),
                Components\TechnicalAndFormatInformationSection::make(),
                Components\ProvenanceAndResearchContextSection::make(),
                Components\RightsAndAccessSection::make(),
                Components\AdditionalNotesSection::make(),
            ])
            ->columns(1);
    }
}
