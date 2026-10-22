<?php

declare(strict_types=1);

namespace SchedulingAttributes\Contracts;

use Illuminate\Console\Scheduling\Event;

/**
 * An attribute applied to every event of the class it decorates: a constraint, an
 * execution option, an output destination or a ping.
 */
interface Modifier
{
    public function apply(Event $event): void;
}
