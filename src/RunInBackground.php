<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\CommandOnly;
use SchedulingAttributes\Contracts\Modifier;

/**
 * Runs the command in the background so that the scheduler does not wait for it.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RunInBackground implements CommandOnly, Modifier
{
    public function apply(Event $event): void
    {
        $event->runInBackground();
    }
}
