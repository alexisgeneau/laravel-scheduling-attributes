<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;

/**
 * Runs even while the application is in maintenance mode.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class EvenInMaintenanceMode implements Modifier
{
    public function apply(Event $event): void
    {
        $event->evenInMaintenanceMode();
    }
}
