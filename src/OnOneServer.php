<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;

/**
 * Runs on a single server when several run the scheduler; needs a shared cache store.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class OnOneServer implements Modifier
{
    public function apply(Event $event): void
    {
        $event->onOneServer();
    }
}
