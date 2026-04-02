<?php

arch('only the feed client talks to http')
    ->expect('HireHq\LaravelBankHolidays\Support')
    ->not->toUse('Illuminate\Support\Facades\Http')
    ->ignoring('HireHq\LaravelBankHolidays\Support\BankHolidayFeedClient');

arch('only the validator throws validation exceptions')
    ->expect('HireHq\LaravelBankHolidays')
    ->not->toUse('HireHq\LaravelBankHolidays\Exceptions\BankHolidayFeedValidationException')
    ->ignoring('HireHq\LaravelBankHolidays\Support\BankHolidayFeedValidator');
