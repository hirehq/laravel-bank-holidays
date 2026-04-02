<?php

use Carbon\CarbonImmutable;
use HireHq\LaravelBankHolidays\Exceptions\BankHolidayFeedException;
use HireHq\LaravelBankHolidays\UkBankHolidayCalendar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

function bankHolidayFeed(array $overrides = []): array
{
    return array_replace_recursive([
        'england-and-wales' => [
            'division' => 'england-and-wales',
            'events' => [],
        ],
        'scotland' => [
            'division' => 'scotland',
            'events' => [],
        ],
        'northern-ireland' => [
            'division' => 'northern-ireland',
            'events' => [],
        ],
    ], $overrides);
}

beforeEach(function () {
    config()->set('cache.default', 'array');
    Cache::flush();
    app()->forgetInstance(UkBankHolidayCalendar::class);
});

test('add working day skips weekends and bank holidays', function () {
    Http::fake([
        'https://www.gov.uk/bank-holidays.json' => Http::response(bankHolidayFeed([
            'england-and-wales' => [
                'events' => [
                    ['date' => '2025-05-26'],
                ],
            ],
        ]), 200, ['Content-Type' => 'application/json']),
    ]);

    $date = CarbonImmutable::parse('2025-05-23');

    expect($date->addWorkingDay()->toDateString())->toBe('2025-05-27');
});

test('sub working day skips weekends and bank holidays', function () {
    Http::fake([
        'https://www.gov.uk/bank-holidays.json' => Http::response(bankHolidayFeed([
            'england-and-wales' => [
                'events' => [
                    ['date' => '2025-05-26'],
                ],
            ],
        ]), 200, ['Content-Type' => 'application/json']),
    ]);

    $date = CarbonImmutable::parse('2025-05-27');

    expect($date->subWorkingDay()->toDateString())->toBe('2025-05-23');
});

test('configured division controls which holidays are skipped', function () {
    config()->set('bank_holidays.division', 'scotland');

    Http::fake([
        'https://www.gov.uk/bank-holidays.json' => Http::response(bankHolidayFeed([
            'england-and-wales' => [
                'events' => [
                    ['date' => '2025-01-01'],
                ],
            ],
            'scotland' => [
                'events' => [
                    ['date' => '2025-01-01'],
                    ['date' => '2025-01-02'],
                ],
            ],
        ]), 200, ['Content-Type' => 'application/json']),
    ]);

    $date = CarbonImmutable::parse('2025-01-01');

    expect($date->addWorkingDay()->toDateString())->toBe('2025-01-03');
});

test('working day and bank holiday helpers expose matching predicates', function () {
    Http::fake([
        'https://www.gov.uk/bank-holidays.json' => Http::response(bankHolidayFeed([
            'england-and-wales' => [
                'events' => [
                    ['date' => '2025-05-26'],
                ],
            ],
        ]), 200, ['Content-Type' => 'application/json']),
    ]);

    $bankHoliday = CarbonImmutable::parse('2025-05-26');
    $weekend = CarbonImmutable::parse('2025-05-24');
    $workingDay = CarbonImmutable::parse('2025-05-27');

    expect($bankHoliday->isBankHoliday())->toBeTrue()
        ->and($bankHoliday->isNotBankHoliday())->toBeFalse()
        ->and($bankHoliday->isWorkingDay())->toBeFalse()
        ->and($bankHoliday->isNotWorkingDay())->toBeTrue()
        ->and($weekend->isBankHoliday())->toBeFalse()
        ->and($weekend->isWorkingDay())->toBeFalse()
        ->and($weekend->isNotWorkingDay())->toBeTrue()
        ->and($workingDay->isBankHoliday())->toBeFalse()
        ->and($workingDay->isNotBankHoliday())->toBeTrue()
        ->and($workingDay->isWorkingDay())->toBeTrue()
        ->and($workingDay->isNotWorkingDay())->toBeFalse();
});

test('bank holiday feed is cached between calendar instances', function () {
    Http::fake([
        'https://www.gov.uk/bank-holidays.json' => Http::response(bankHolidayFeed([
            'england-and-wales' => [
                'events' => [
                    ['date' => '2025-05-26'],
                    ['date' => '2025-12-25'],
                ],
            ],
        ]), 200, ['Content-Type' => 'application/json']),
    ]);

    CarbonImmutable::parse('2025-05-23')->addWorkingDay();

    app()->forgetInstance(UkBankHolidayCalendar::class);

    CarbonImmutable::parse('2025-12-24')->addWorkingDay();

    Http::assertSentCount(1);
});

test('uses the last known good cached feed when a later response is invalid', function () {
    Http::fake([
        'https://www.gov.uk/bank-holidays.json' => Http::sequence()
            ->push(bankHolidayFeed([
                'england-and-wales' => [
                    'events' => [
                        ['date' => '2025-05-26'],
                    ],
                ],
            ]), 200, ['Content-Type' => 'application/json'])
            ->push('not-json', 200, ['Content-Type' => 'text/plain']),
    ]);

    expect(CarbonImmutable::parse('2025-05-23')->addWorkingDay()->toDateString())->toBe('2025-05-27');

    Cache::forget('uk-bank-holidays.feed');
    app()->forgetInstance(UkBankHolidayCalendar::class);

    expect(CarbonImmutable::parse('2025-05-23')->addWorkingDay()->toDateString())->toBe('2025-05-27');
});

test('ignores custom feed urls and always requests the gov uk endpoint', function () {
    config()->set('bank_holidays.feed_url', 'https://example.com/bank-holidays.json');

    Http::fake([
        'https://www.gov.uk/bank-holidays.json' => Http::response(bankHolidayFeed(), 200, ['Content-Type' => 'application/json']),
        'https://example.com/*' => Http::response('should-not-be-called', 200, ['Content-Type' => 'application/json']),
    ]);

    CarbonImmutable::parse('2025-05-27')->isWorkingDay();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://www.gov.uk/bank-holidays.json';
    });
});

test('rejects invalid feed payloads when there is no known good cache', function () {
    Http::fake([
        'https://www.gov.uk/bank-holidays.json' => Http::response([
            'england-and-wales' => [
                'division' => 'england-and-wales',
                'events' => [
                    ['date' => '2025/05/26'],
                ],
            ],
            'scotland' => [
                'division' => 'scotland',
                'events' => [],
            ],
            'northern-ireland' => [
                'division' => 'northern-ireland',
                'events' => [],
            ],
        ], 200, ['Content-Type' => 'application/json']),
    ]);

    CarbonImmutable::parse('2025-05-27')->isWorkingDay();
})->throws(BankHolidayFeedException::class, 'Unable to retrieve a valid UK bank holiday feed.');
