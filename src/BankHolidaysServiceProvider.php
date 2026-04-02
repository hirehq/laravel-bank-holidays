<?php

namespace HireHq\LaravelBankHolidays;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use HireHq\LaravelBankHolidays\Support\BankHolidayFeedClient;
use HireHq\LaravelBankHolidays\Support\BankHolidayFeedValidator;
use Illuminate\Support\ServiceProvider;

final class BankHolidaysServiceProvider extends ServiceProvider
{
    public $singletons = [
        BankHolidayFeedClient::class => BankHolidayFeedClient::class,
        BankHolidayFeedValidator::class => BankHolidayFeedValidator::class,
        UkBankHolidayCalendar::class => UkBankHolidayCalendar::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bank_holidays.php', 'bank_holidays');
    }

    public function boot(): void
    {
        $this->registerDateMacros();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/bank_holidays.php' => config_path('bank_holidays.php'),
            ], 'bank-holidays-config');
        }
    }

    private function registerDateMacros(): void
    {
        foreach ([Carbon::class, CarbonImmutable::class] as $dateClass) {
            $dateClass::macro('addWorkingDay', function () {
                return app(UkBankHolidayCalendar::class)->addWorkingDay($this);
            });

            $dateClass::macro('subWorkingDay', function () {
                return app(UkBankHolidayCalendar::class)->subWorkingDay($this);
            });

            $dateClass::macro('isWorkingDay', function () {
                return app(UkBankHolidayCalendar::class)->isWorkingDay($this);
            });

            $dateClass::macro('isNotWorkingDay', function () {
                return app(UkBankHolidayCalendar::class)->isNotWorkingDay($this);
            });

            $dateClass::macro('isBankHoliday', function () {
                return app(UkBankHolidayCalendar::class)->isBankHoliday($this);
            });

            $dateClass::macro('isNotBankHoliday', function () {
                return app(UkBankHolidayCalendar::class)->isNotBankHoliday($this);
            });
        }
    }
}
