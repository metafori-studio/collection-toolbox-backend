<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Metafori\Core\Data\FuzzyDate;
use Metafori\Core\Enums\DatePrecision;
use Metafori\Core\Traits\HasFuzzyDates;

class TestFuzzyDateModel extends Model
{
    use HasFuzzyDates;

    protected $guarded = [];

    protected $dateFormat = 'Y-m-d H:i:s';

    protected function casts(): array
    {
        return [
            'date' => FuzzyDate::class,
        ];
    }
}

test('creates single fuzzy date via factory with calculated bounds', function () {
    $date = FuzzyDate::single('1920-01-01', DatePrecision::Year);

    expect($date->start->toDateString())->toBe('1920-01-01')
        ->and($date->end->toDateString())->toBe('1920-12-31')
        ->and($date->isRange)->toBeFalse()
        ->and($date->startPrecision)->toBe(DatePrecision::Year)
        ->and($date->endPrecision)->toBe(DatePrecision::Year)
        ->and($date->toArray())->toBe([
            'is_range' => false,
            'date' => '1920-01-01',
            'precision' => 'year',
        ]);
});

test('creates fuzzy date range via factory', function () {
    $range = FuzzyDate::range('1920-01-01', DatePrecision::Year, '1925-05-31', DatePrecision::Month);

    expect($range->start->toDateString())->toBe('1920-01-01')
        ->and($range->end->toDateString())->toBe('1925-05-31')
        ->and($range->isRange)->toBeTrue()
        ->and($range->startPrecision)->toBe(DatePrecision::Year)
        ->and($range->endPrecision)->toBe(DatePrecision::Month)
        ->and($range->toArray())->toBe([
            'is_range' => true,
            'start' => [
                'date' => '1920-01-01',
                'precision' => 'year',
            ],
            'end' => [
                'date' => '1925-05-31',
                'precision' => 'month',
            ],
        ]);
});

test('instantiates with separate start and end precision', function () {
    $date = new FuzzyDate(
        start: Carbon::parse('1920-01-01'),
        end: Carbon::parse('1925-05-31'),
        isRange: true,
        startPrecision: DatePrecision::Year,
        endPrecision: DatePrecision::Month,
    );

    expect($date->start->toDateString())->toBe('1920-01-01')
        ->and($date->end->toDateString())->toBe('1925-05-31')
        ->and($date->isRange)->toBeTrue()
        ->and($date->startPrecision)->toBe(DatePrecision::Year)
        ->and($date->endPrecision)->toBe(DatePrecision::Month);
});

test('constructs from database with separate start and end precision settings for range', function () {
    $date = FuzzyDate::fromDatabase('1920-01-01', '1925-05-31', [
        'is_range' => true,
        'start_precision' => 'year',
        'end_precision' => 'month',
    ]);

    expect($date)->not->toBeNull()
        ->and($date->startPrecision)->toBe(DatePrecision::Year)
        ->and($date->endPrecision)->toBe(DatePrecision::Month)
        ->and($date->isRange)->toBeTrue();
});

test('constructs from database with precision setting for single date', function () {
    $date = FuzzyDate::fromDatabase('1920-01-01', '1920-12-31', [
        'is_range' => false,
        'precision' => 'year',
    ]);

    expect($date)->not->toBeNull()
        ->and($date->startPrecision)->toBe(DatePrecision::Year)
        ->and($date->endPrecision)->toBe(DatePrecision::Year)
        ->and($date->isRange)->toBeFalse();
});

test('from database returns null when both dates are null', function () {
    expect(FuzzyDate::fromDatabase(null, null, ['is_range' => false]))->toBeNull();
});

test('casts fuzzy date to and from model attributes', function () {
    $model = new TestFuzzyDateModel;

    $model->date = FuzzyDate::range('1930-01-01', DatePrecision::Year, '1935-12-31', DatePrecision::Day);

    expect($model->getAttributes()['date_start'])->toBe('1930-01-01')
        ->and($model->getAttributes()['date_end'])->toBe('1935-12-31')
        ->and(json_decode($model->getAttributes()['date_settings'], true))->toBe([
            'is_range' => true,
            'start_precision' => 'year',
            'end_precision' => 'day',
        ]);

    $reloaded = $model->date;
    expect($reloaded)->toBeInstanceOf(FuzzyDate::class)
        ->and($reloaded->startPrecision)->toBe(DatePrecision::Year)
        ->and($reloaded->endPrecision)->toBe(DatePrecision::Day);
});

test('casts single fuzzy date storing is_range and precision in settings', function () {
    $model = new TestFuzzyDateModel;

    $model->date = FuzzyDate::single('1920-01-01', DatePrecision::Year);

    expect($model->getAttributes()['date_start'])->toBe('1920-01-01')
        ->and($model->getAttributes()['date_end'])->toBe('1920-12-31')
        ->and(json_decode($model->getAttributes()['date_settings'], true))->toBe([
            'is_range' => false,
            'precision' => 'year',
        ]);

    expect($model->date->isRange)->toBeFalse();
});

test('throws exception when setting non-FuzzyDate value on model attribute', function () {
    $model = new TestFuzzyDateModel;

    expect(fn () => $model->date = [
        'is_range' => false,
        'date' => '1945-05-09',
        'precision' => 'day',
    ])->toThrow(InvalidArgumentException::class, 'Value must be an instance of FuzzyDate or null.');
});

test('sets null on model attributes when date is set to null', function () {
    $model = new TestFuzzyDateModel([
        'date_start' => '1930-01-01',
        'date_end' => '1930-12-31',
        'date_settings' => json_encode(['is_range' => false, 'precision' => 'year']),
    ]);

    $model->date = null;

    expect($model->getAttributes()['date_start'])->toBeNull()
        ->and($model->getAttributes()['date_end'])->toBeNull()
        ->and($model->getAttributes()['date_settings'])->toBeNull();
});

test('round trips serialized open-ended fuzzy date ranges', function (?string $start, ?string $end) {
    $date = FuzzyDate::fromDatabase($start, $end, [
        'is_range' => true,
        'start_precision' => 'year',
        'end_precision' => 'month',
    ]);
    $model = new TestFuzzyDateModel;
    $model->date = $date;

    expect($model->getAttributes()['date_start'])->toBe($start)
        ->and($model->getAttributes()['date_end'])->toBe($end)
        ->and($model->date->toArray())->toBe($date->toArray());
})->with([
    'end only' => [null, '1950-05-31'],
    'start only' => ['1920-01-01', null],
]);
