<?php

namespace Metafori\Etno\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Metafori\Etno\Enums\ItemType;

class CitationFormatter
{
    public static function format(
        ?string $title,
        ?string $subtitle,
        Collection $authors,
        Collection $originators,
        ?\DateTimeInterface $publicationDate,
        ?string $institutionName,
        ?ItemType $type,
        ?\DateTimeInterface $timePeriodStart,
        ?\DateTimeInterface $timePeriodEnd,
        ?string $doi,
    ): ?string {
        $namesAndYear = self::formatNamesAndYear($authors, $originators, $publicationDate);
        $instName = self::formatInstitution($institutionName);

        $titleAndSubtitle = self::formatTitleSection($title, $subtitle);

        $formattedTypeLabel = null;
        if ($type) {
            $formattedTypeLabel = Str::ucfirst($type->getLabel());
        }

        $timePeriod = self::formatTimePeriod($timePeriodStart, $timePeriodEnd);

        $parts = [];

        if ($namesAndYear !== '') {
            $parts[] = $namesAndYear;
        }

        if ($titleAndSubtitle !== '') {
            $parts[] = "{$titleAndSubtitle}.";
        }

        if ($instName) {
            $parts[] = "{$instName}.";
        }

        if ($formattedTypeLabel) {
            $parts[] = "({$formattedTypeLabel}).";
        }

        if ($timePeriod !== '') {
            $parts[] = '('.__('etno::ui.citations.time_period').": {$timePeriod}).";
        }

        if ($doi) {
            $parts[] = "https://doi.org/{$doi}";
        }

        return $parts ? \implode(' ', $parts) : null;
    }

    private static function formatNames(Collection $authors, Collection $originators): string
    {
        $authorsStr = $authors->join(', ', ' & ');
        $originatorsStr = $originators->join(', ', ' & ');

        $namesStr = '';
        if ($authorsStr !== '') {
            $namesStr = $authorsStr;
            if ($originatorsStr !== '') {
                $namesStr .= ', '.__('etno::ui.citations.originator').': '.$originatorsStr;
            }
        } else {
            $namesStr = $originatorsStr;
        }

        return $namesStr;
    }

    private static function formatTitleSection(?string $title, ?string $subtitle): string
    {
        $titleAndSubtitle = '';
        if ($title) {
            $titleAndSubtitle = $subtitle ? "{$title}: {$subtitle}" : $title;
        }

        return $titleAndSubtitle;
    }

    private static function formatNamesAndYear(
        Collection $authors,
        Collection $originators,
        ?\DateTimeInterface $publicationDate,
    ): string {
        $namesStr = self::formatNames($authors, $originators);
        $publicationYear = $publicationDate?->format('Y');

        $namesAndYear = '';
        if ($namesStr !== '') {
            $namesAndYear = $namesStr;
        }
        if ($publicationYear) {
            $namesAndYear .= ($namesAndYear !== '' ? ' ' : '')."({$publicationYear}):";
        } elseif ($namesAndYear !== '') {
            $namesAndYear .= ':';
        }

        return $namesAndYear;
    }

    private static function formatInstitution(?string $institutionName): ?string
    {
        $instName = $institutionName;
        if ($instName) {
            if (\str_ends_with($instName, '.')) {
                $instName = \substr($instName, 0, -1);
            }
        }

        return $instName;
    }

    private static function formatTimePeriod(?\DateTimeInterface $timePeriodStart, ?\DateTimeInterface $timePeriodEnd): string
    {
        $timePeriodStartYear = $timePeriodStart?->format('Y');
        $timePeriodEndYear = $timePeriodEnd?->format('Y');

        $timePeriod = '';
        if ($timePeriodStartYear && $timePeriodEndYear) {
            $timePeriod = $timePeriodStartYear === $timePeriodEndYear
                ? $timePeriodStartYear
                : "{$timePeriodStartYear}–{$timePeriodEndYear}";
        } elseif ($timePeriodStartYear) {
            $timePeriod = $timePeriodStartYear;
        } elseif ($timePeriodEndYear) {
            $timePeriod = $timePeriodEndYear;
        }

        return $timePeriod;
    }
}
