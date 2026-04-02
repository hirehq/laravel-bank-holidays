<?php

return [
    'division' => env('BANK_HOLIDAYS_DIVISION', env('APP_BANK_HOLIDAYS_DIVISION', 'england-and-wales')),
    'cache_days' => (int) env('BANK_HOLIDAYS_CACHE_DAYS', env('APP_BANK_HOLIDAYS_CACHE_DAYS', 31)),
    'timezone' => env('BANK_HOLIDAYS_TIMEZONE'),
];
