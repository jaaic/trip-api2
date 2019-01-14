<?php

namespace App\Core;

/**
 * Class Constants
 *
 * @package App\Core
 * @author  Jaai Chandekar
 */
class Constants
{
    const ERROR_STATE   = 'error';
    const SUCCESS_STATE = 'success';

    // Activities constants
    const DEFAULT_CITY           = 'berlin';
    const DEFAULT_COUNTRY        = 'germany';
    const HOURS_PER_DAY          = 12;
    const MIN_BUDGET_PER_DAY     = 50;
    const MIN_ACTIVITIES_PER_DAY = 3;
    const COMMUTE_MINS           = 30;
    const MIN_ACTIVITY_MINS      = 30;
    const MIN_ACTIVITY_PRICE     = 5;
    const ACTIVITY_START_TIME    = '10:00';
}