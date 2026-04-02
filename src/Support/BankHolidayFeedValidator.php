<?php

namespace HireHq\LaravelBankHolidays\Support;

use Carbon\CarbonImmutable;
use HireHq\LaravelBankHolidays\Exceptions\BankHolidayFeedValidationException;
use Throwable;

final class BankHolidayFeedValidator
{
    /**
     * @param  array<mixed>  $feed
     * @return array<string, array{division: string, events: array<int, array{date: string}>}>
     */
    public function validate(array $feed): array
    {
        $validatedFeed = [];

        foreach (BankHolidayConfig::DIVISIONS as $division) {
            $payload = $feed[$division] ?? null;

            if (! is_array($payload)) {
                throw new BankHolidayFeedValidationException('The UK bank holiday feed is missing a required division payload.');
            }

            if (($payload['division'] ?? null) !== $division) {
                throw new BankHolidayFeedValidationException('The UK bank holiday feed contains an invalid division identifier.');
            }

            $events = $payload['events'] ?? null;

            if (! is_array($events)) {
                throw new BankHolidayFeedValidationException('The UK bank holiday feed contains an invalid events payload.');
            }

            $validatedEvents = [];

            foreach ($events as $event) {
                if (! is_array($event)) {
                    throw new BankHolidayFeedValidationException('The UK bank holiday feed contains an invalid event.');
                }

                $date = $event['date'] ?? null;

                if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    throw new BankHolidayFeedValidationException('The UK bank holiday feed contains an invalid holiday date.');
                }

                try {
                    $parsedDate = CarbonImmutable::parse($date);
                } catch (Throwable) {
                    throw new BankHolidayFeedValidationException('The UK bank holiday feed contains an unreadable holiday date.');
                }

                if ($parsedDate->format('Y-m-d') !== $date) {
                    throw new BankHolidayFeedValidationException('The UK bank holiday feed contains a non-canonical holiday date.');
                }

                $validatedEvents[] = [
                    'date' => $date,
                ];
            }

            $validatedFeed[$division] = [
                'division' => $division,
                'events' => $validatedEvents,
            ];
        }

        return $validatedFeed;
    }
}
