<?php

declare(strict_types=1);

namespace SchedulingAttributes\Support;

use RuntimeException;

/**
 * Thrown by an attribute whose scheduler method does not exist in the installed
 * version of Laravel; the registrar turns it into a ScheduleAttributeException.
 */
final class UnsupportedMethod extends RuntimeException
{
    public function __construct(public readonly string $method)
    {
        parent::__construct(sprintf('Schedule::%s() is not available.', $method));
    }
}
