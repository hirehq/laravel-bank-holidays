<?php

namespace HireHq\LaravelBankHolidays;

use Carbon\CarbonInterface;
use HireHq\LaravelBankHolidays\Exceptions\BankHolidayFeedException;
use HireHq\LaravelBankHolidays\Support\BankHolidayConfig;
use HireHq\LaravelBankHolidays\Support\BankHolidayFeedClient;
use HireHq\LaravelBankHolidays\Support\BankHolidayFeedValidator;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Throwable;

final class UkBankHolidayCalendar
{
    private const array DIVISIONS = [
        'england-and-wales',
        'scotland',
        'northern-ireland',
    ];

    private array $bankHolidaysByDivision = [];

    public function __construct(
        private readonly BankHolidayFeedClient $feedClient,
        private readonly BankHolidayFeedValidator $feedValidator,
    ) {}

    public function addWorkingDay(CarbonInterface $date): CarbonInterface
    {
        return $this->shiftWorkingDay($date, 1);
    }

    public function subWorkingDay(CarbonInterface $date): CarbonInterface
    {
        return $this->shiftWorkingDay($date, -1);
    }

    public function isWorkingDay(CarbonInterface $date): bool
    {
        $localDate = $date->copy()->tz(BankHolidayConfig::timezone());

        if ($localDate->isWeekend()) {
            return false;
        }

        return ! $this->isBankHoliday($localDate);
    }

    public function isNotWorkingDay(CarbonInterface $date): bool
    {
        return ! $this->isWorkingDay($date);
    }

    public function isBankHoliday(CarbonInterface $date): bool
    {
        $localDate = $date->copy()->tz(BankHolidayConfig::timezone());

        return isset($this->bankHolidays($this->resolveDivision())[$localDate->toDateString()]);
    }

    public function isNotBankHoliday(CarbonInterface $date): bool
    {
        return ! $this->isBankHoliday($date);
    }

    private function shiftWorkingDay(CarbonInterface $date, int $direction): CarbonInterface
    {
        $candidate = $direction > 0 ? $date->addDay() : $date->subDay();

        while (! $this->isWorkingDay($candidate)) {
            $candidate = $direction > 0 ? $candidate->addDay() : $candidate->subDay();
        }

        return $candidate;
    }

    private function bankHolidays(string $division): array
    {
        if (array_key_exists($division, $this->bankHolidaysByDivision)) {
            return $this->bankHolidaysByDivision[$division];
        }

        $events = $this->feed()[$division]['events'] ?? [];
        $bankHolidays = [];

        foreach ($events as $event) {
            $date = $event['date'] ?? null;

            if (is_string($date)) {
                $bankHolidays[$date] = true;
            }
        }

        return $this->bankHolidaysByDivision[$division] = $bankHolidays;
    }

    private function feed(): array
    {
        $cachedFeed = Cache::get(BankHolidayConfig::cacheKey());

        if (is_array($cachedFeed)) {
            return $cachedFeed;
        }

        try {
            $feed = $this->requestFeed();

            Cache::put(BankHolidayConfig::cacheKey(), $feed, now()->addDays(BankHolidayConfig::cacheDays()));
            Cache::put(BankHolidayConfig::staleCacheKey(), $feed, now()->addDays(BankHolidayConfig::staleCacheDays()));

            return $feed;
        } catch (Throwable $exception) {
            $staleFeed = Cache::get(BankHolidayConfig::staleCacheKey());

            if (is_array($staleFeed)) {
                return $staleFeed;
            }

            throw new BankHolidayFeedException('Unable to retrieve a valid UK bank holiday feed.', $exception->getCode(), previous: $exception);
        }
    }

    private function resolveDivision(): string
    {
        $division = BankHolidayConfig::division();

        if (! in_array($division, self::DIVISIONS, true)) {
            throw new InvalidArgumentException('The configured UK bank holiday division is invalid.');
        }

        return $division;
    }

    private function requestFeed(): array
    {
        return $this->feedValidator->validate($this->feedClient->fetch());
    }
}
