<?php

use Carbon\Carbon;
use Metafori\Core\Tests\TestCase;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Support\CitationFormatter;

uses(TestCase::class);

beforeEach(function () {
    app()->setLocale('sk');
});

it('can generate a complete citation with all fields', function () {
    $result = CitationFormatter::format(
        title: 'Terénny výskum v obci Hriňová',
        subtitle: 'Analýza rodinných väzieb',
        authors: collect(['Milan Kováč']),
        originators: collect(['Jozef Mrkvička']),
        publicationDate: Carbon::parse('2026-03-15'),
        institutionName: 'Ústav etnológie a sociálnej antropológie SAV, v. v. i.',
        type: ItemType::ResearchReport,
        timePeriodStart: Carbon::parse('1953-06-20'),
        timePeriodEnd: null,
    );

    $expected = 'Milan Kováč, pôvodca: Jozef Mrkvička (2026): Terénny výskum v obci Hriňová: Analýza rodinných väzieb. Ústav etnológie a sociálnej antropológie SAV, v. v. i. (Výskumná správa). (Rok realizácie: 1953).';
    expect($result)->toBe($expected);
});

it('omits originator prefix when only originator is present', function () {
    $result = CitationFormatter::format(
        title: 'Názov',
        subtitle: null,
        authors: collect([]),
        originators: collect(['Jozef Mrkvička']),
        publicationDate: Carbon::parse('2026-03-15'),
        institutionName: null,
        type: null,
        timePeriodStart: null,
        timePeriodEnd: null,
    );

    expect($result)->toBe('Jozef Mrkvička (2026): Názov.');
});

it('combines multiple authors with ampersand and comma', function () {
    $result = CitationFormatter::format(
        title: 'Názov',
        subtitle: null,
        authors: collect(['Milan Kováč', 'Ján Novák', 'Jozef Mrkvička']),
        originators: collect([]),
        publicationDate: Carbon::parse('2026-03-15'),
        institutionName: null,
        type: null,
        timePeriodStart: null,
        timePeriodEnd: null,
    );

    expect($result)->toBe('Milan Kováč, Ján Novák & Jozef Mrkvička (2026): Názov.');
});

it('omits colon and space when subtitle is missing', function () {
    $result = CitationFormatter::format(
        title: 'Názov',
        subtitle: null,
        authors: collect(['Milan Kováč']),
        originators: collect([]),
        publicationDate: Carbon::parse('2026-03-15'),
        institutionName: null,
        type: null,
        timePeriodStart: null,
        timePeriodEnd: null,
    );

    expect($result)->toBe('Milan Kováč (2026): Názov.');
});

it('omits realization year block completely when time_period_start is missing', function () {
    $result = CitationFormatter::format(
        title: 'Názov',
        subtitle: null,
        authors: collect(['Milan Kováč']),
        originators: collect([]),
        publicationDate: Carbon::parse('2026-03-15'),
        institutionName: null,
        type: ItemType::ResearchReport,
        timePeriodStart: null,
        timePeriodEnd: null,
    );

    expect($result)->toBe('Milan Kováč (2026): Názov. (Výskumná správa).');
});

it('formats realization year as single year if only time_period_end exists', function () {
    $result = CitationFormatter::format(
        title: 'Názov',
        subtitle: null,
        authors: collect(['Milan Kováč']),
        originators: collect([]),
        publicationDate: Carbon::parse('2026-03-15'),
        institutionName: null,
        type: ItemType::ResearchReport,
        timePeriodStart: null,
        timePeriodEnd: Carbon::parse('1955-08-12'),
    );

    expect($result)->toBe('Milan Kováč (2026): Názov. (Výskumná správa). (Rok realizácie: 1955).');
});

it('formats realization year as range with en-dash when both start and end exist and differ', function () {
    $result = CitationFormatter::format(
        title: 'Názov',
        subtitle: null,
        authors: collect(['Milan Kováč']),
        originators: collect([]),
        publicationDate: Carbon::parse('2026-03-15'),
        institutionName: null,
        type: ItemType::ResearchReport,
        timePeriodStart: Carbon::parse('1953-06-20'),
        timePeriodEnd: Carbon::parse('1955-08-12'),
    );

    expect($result)->toBe('Milan Kováč (2026): Názov. (Výskumná správa). (Rok realizácie: 1953–1955).');
});

it('formats realization year as single year if start and end are in the same year', function () {
    $result = CitationFormatter::format(
        title: 'Názov',
        subtitle: null,
        authors: collect(['Milan Kováč']),
        originators: collect([]),
        publicationDate: Carbon::parse('2026-03-15'),
        institutionName: null,
        type: ItemType::ResearchReport,
        timePeriodStart: Carbon::parse('1953-06-20'),
        timePeriodEnd: Carbon::parse('1953-12-25'),
    );

    expect($result)->toBe('Milan Kováč (2026): Názov. (Výskumná správa). (Rok realizácie: 1953).');
});

it('can generate a complete citation with all fields in English', function () {
    app()->setLocale('en');

    $result = CitationFormatter::format(
        title: 'Terénny výskum v obci Hriňová',
        subtitle: 'Analýza rodinných väzieb',
        authors: collect(['Milan Kováč']),
        originators: collect(['Jozef Mrkvička']),
        publicationDate: Carbon::parse('2026-03-15'),
        institutionName: 'Ústav etnológie a sociálnej antropológie SAV, v. v. i.',
        type: ItemType::ResearchReport,
        timePeriodStart: Carbon::parse('1953-06-20'),
        timePeriodEnd: Carbon::parse('1955-08-12'),
    );

    $expected = 'Milan Kováč, originator: Jozef Mrkvička (2026): Terénny výskum v obci Hriňová: Analýza rodinných väzieb. Ústav etnológie a sociálnej antropológie SAV, v. v. i. (Research report). (Time Period: 1953–1955).';
    expect($result)->toBe($expected);
});

it('capitalizes the first letter of the citation when originator starts with a lowercase letter', function () {
    $result = CitationFormatter::format(
        title: 'Názov',
        subtitle: null,
        authors: collect([]),
        originators: collect(['učitelia základných škôl']),
        publicationDate: Carbon::parse('2026-03-15'),
        institutionName: null,
        type: null,
        timePeriodStart: null,
        timePeriodEnd: null,
    );

    expect($result)->toBe('Učitelia základných škôl (2026): Názov.');
});
