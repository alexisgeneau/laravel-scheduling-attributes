<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures;

/**
 * What the fixtures executed, for the tests that run the schedule.
 */
final class Ran
{
    /** @var list<string> */
    public static array $jobs = [];

    public static function reset(): void
    {
        self::$jobs = [];
    }
}
