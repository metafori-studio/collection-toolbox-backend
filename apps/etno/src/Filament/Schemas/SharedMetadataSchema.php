<?php

namespace Metafori\Etno\Filament\Schemas;

trait SharedMetadataSchema
{
    protected static function components(bool $inheritable = false): array
    {
        return [
            Components\BasicInformationSection::make()->inheritable($inheritable),
            Components\DescriptiveInformationSection::make()->inheritable($inheritable),
            Components\AuthorsAndCreatorsSection::make()->inheritable($inheritable),
            Components\GeographicInformationSection::make()->inheritable($inheritable),
            Components\TechnicalAndFormatInformationSection::make()->inheritable($inheritable),
            Components\ProvenanceAndResearchContextSection::make()->inheritable($inheritable),
            Components\RightsAndAccessSection::make()->inheritable($inheritable),
            Components\AdditionalNotesSection::make()->inheritable($inheritable),
        ];
    }
}
