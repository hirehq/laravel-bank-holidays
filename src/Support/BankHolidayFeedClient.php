<?php

namespace HireHq\LaravelBankHolidays\Support;

use HireHq\LaravelBankHolidays\Exceptions\BankHolidayFeedException;
use Illuminate\Support\Facades\Http;

final class BankHolidayFeedClient
{
    private const string FEED_URL = 'https://www.gov.uk/bank-holidays.json';

    private const int MAX_FEED_BYTES = 262144;

    /**
     * @return array<mixed>
     */
    public function fetch(): array
    {
        $response = Http::acceptJson()
            ->timeout(10)
            ->withoutRedirecting()
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->get(self::FEED_URL)
            ->throw();

        $contentType = mb_strtolower((string) $response->header('Content-Type'));

        if (! str_contains($contentType, 'json')) {
            throw new BankHolidayFeedException('The UK bank holiday feed returned an unexpected content type.');
        }

        $body = $response->body();

        if (mb_strlen($body) > self::MAX_FEED_BYTES) {
            throw new BankHolidayFeedException('The UK bank holiday feed exceeded the maximum expected size.');
        }

        $feed = json_decode($body, true);

        if (! is_array($feed)) {
            throw new BankHolidayFeedException('The UK bank holiday feed did not return a valid JSON payload.');
        }

        return $feed;
    }
}
