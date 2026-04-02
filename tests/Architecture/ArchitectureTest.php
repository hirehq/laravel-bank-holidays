<?php

use HireHq\LaravelBankHolidays\Exceptions\BankHolidayFeedValidationException;
use HireHq\LaravelBankHolidays\Support\BankHolidayFeedClient;
use HireHq\LaravelBankHolidays\Support\BankHolidayFeedValidator;
use Illuminate\Support\Facades\Http;

arch('only the feed client talks to http')
    ->expect('HireHq\LaravelBankHolidays\Support')
    ->not->toUse(Http::class)
    ->ignoring(BankHolidayFeedClient::class);

arch('only the validator throws validation exceptions')
    ->expect('HireHq\LaravelBankHolidays')
    ->not->toUse(BankHolidayFeedValidationException::class)
    ->ignoring(BankHolidayFeedValidator::class);
