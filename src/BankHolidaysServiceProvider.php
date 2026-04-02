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
            $dateClass::macro('addWorkingDay', fn() => resolve(UkBankHolidayCalendar::class)->addWorkingDay($this));

            $dateClass::macro('subWorkingDay', fn() => resolve(UkBankHolidayCalendar::class)->subWorkingDay($this));

            $dateClass::macro('isWorkingDay', fn() => resolve(UkBankHolidayCalendar::class)->isWorkingDay($this));

            $dateClass::macro('isNotWorkingDay', fn() => resolve(UkBankHolidayCalendar::class)->isNotWorkingDay($this));

            $dateClass::macro('isBankHoliday', fn() => resolve(UkBankHolidayCalendar::class)->isBankHoliday($this));

            $dateClass::macro('isNotBankHoliday', fn() => resolve(UkBankHolidayCalendar::class)->isNotBankHoliday($this));
        }
    }
}
