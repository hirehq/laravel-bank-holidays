<?php

namespace HireHq\LaravelBankHolidays\Tests;

use HireHq\LaravelBankHolidays\BankHolidaysServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BankHolidaysServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('app.timezone', 'UTC');
        $app['config']->set('app.timezone_display', 'Europe/London');
    }
}
