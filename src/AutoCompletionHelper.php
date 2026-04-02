<?php

namespace Illuminate\Support;

/**
 * @method static addWorkingDay()
 * @method static addWorkingDays(int $days = 1)
 * @method static subWorkingDay()
 * @method static subWorkingDays(int $days = 1)
 * @method bool isWorkingDay()
 * @method bool isNotWorkingDay()
 * @method bool isBankHoliday()
 * @method bool isNotBankHoliday()
 */
abstract class Carbon extends \Carbon\Carbon {}

namespace Carbon;

/**
 * @method static addWorkingDay()
 * @method static addWorkingDays(int $days = 1)
 * @method static subWorkingDay()
 * @method static subWorkingDays(int $days = 1)
 * @method bool isWorkingDay()
 * @method bool isNotWorkingDay()
 * @method bool isBankHoliday()
 * @method bool isNotBankHoliday()
 */
abstract class Carbon extends DateTime implements CarbonInterface {}

/**
 * @method static addWorkingDay()
 * @method static addWorkingDays(int $days = 1)
 * @method static subWorkingDay()
 * @method static subWorkingDays(int $days = 1)
 * @method bool isWorkingDay()
 * @method bool isNotWorkingDay()
 * @method bool isBankHoliday()
 * @method bool isNotBankHoliday()
 */
abstract class CarbonImmutable extends DateTime implements CarbonInterface {}
