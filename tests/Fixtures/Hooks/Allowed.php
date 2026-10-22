<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Hooks;

/**
 * A condition the tests flip.
 */
final class Allowed
{
    public static bool $value = true;

    public function __invoke(): bool
    {
        return self::$value;
    }
}
