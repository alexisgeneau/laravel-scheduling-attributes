<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Illuminate\Console\Scheduling\Schedule;

/**
 * Days of the week, with the values Laravel's scheduler uses (Sunday is 0).
 */
enum Day: int
{
    case Sunday = Schedule::SUNDAY;
    case Monday = Schedule::MONDAY;
    case Tuesday = Schedule::TUESDAY;
    case Wednesday = Schedule::WEDNESDAY;
    case Thursday = Schedule::THURSDAY;
    case Friday = Schedule::FRIDAY;
    case Saturday = Schedule::SATURDAY;
}
