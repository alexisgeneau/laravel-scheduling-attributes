<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;

/**
 * Only on Saturdays.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Saturdays implements Modifier
{
    public function apply(Event $event): void
    {
        $event->saturdays();
    }
}
