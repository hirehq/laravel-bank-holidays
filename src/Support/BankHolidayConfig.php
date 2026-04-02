<?php

namespace HireHq\LaravelBankHolidays\Support;

final class BankHolidayConfig
{
    public const DIVISIONS = [
        'england-and-wales',
        'scotland',
        'northern-ireland',
    ];

    public static function cacheKey(): string
    {
        return 'uk-bank-holidays.feed';
    }

    public static function staleCacheKey(): string
    {
        return 'uk-bank-holidays.feed.stale';
    }

    public static function division(): string
    {
        return (string) config('bank_holidays.division', config('app.bank_holidays.division', 'england-and-wales'));
    }

    public static function cacheDays(): int
    {
        return max(1, (int) config('bank_holidays.cache_days', config('app.bank_holidays.cache_days', 31)));
    }

    public static function staleCacheDays(): int
    {
        return max(self::cacheDays() + 31, 62);
    }

    public static function timezone(): string
    {
        return (string) (config('bank_holidays.timezone') ?: config('app.timezone_display') ?: config('app.timezone', 'UTC'));
    }
}
