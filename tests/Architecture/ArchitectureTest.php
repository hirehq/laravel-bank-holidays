<?php

use Illuminate\Support\Facades\Http;
use HireHq\LaravelBankHolidays\Support\BankHolidayFeedClient;
use HireHq\LaravelBankHolidays\Exceptions\BankHolidayFeedValidationException;
use HireHq\LaravelBankHolidays\Support\BankHolidayFeedValidator;

arch('only the feed client talks to http')
    ->expect('HireHq\LaravelBankHolidays\Support')
    ->not->toUse(Http::class)
    ->ignoring(BankHolidayFeedClient::class);

arch('only the validator throws validation exceptions')
    ->expect('HireHq\LaravelBankHolidays')
    ->not->toUse(BankHolidayFeedValidationException::class)
    ->ignoring(BankHolidayFeedValidator::class);
