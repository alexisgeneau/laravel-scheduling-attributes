<?php

declare(strict_types=1);

namespace SchedulingAttributes\Contracts;

use Illuminate\Console\Scheduling\Event;

/**
 * An attribute that creates one scheduled event: the frequency it carries is the
 * frequency the scheduler's fluent API would receive.
 */
interface Frequency
{
    public function apply(Event $event): void;

    /**
     * Arguments and options passed to the command for this event.
     *
     * @return list<string>
     */
    public function arguments(): array;
}
