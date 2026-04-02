<?php

return [
    'division' => env('BANK_HOLIDAYS_DIVISION', env('WORKING_DAYS_DIVISION', env('APP_BANK_HOLIDAYS_DIVISION', 'england-and-wales'))),
    'cache_days' => (int) env('BANK_HOLIDAYS_CACHE_DAYS', env('WORKING_DAYS_CACHE_DAYS', env('APP_BANK_HOLIDAYS_CACHE_DAYS', 31))),
    'timezone' => env('BANK_HOLIDAYS_TIMEZONE', env('WORKING_DAYS_TIMEZONE')),
];
