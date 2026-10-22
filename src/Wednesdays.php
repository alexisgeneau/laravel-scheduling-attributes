<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;

/**
 * Only on Wednesdays.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Wednesdays implements Modifier
{
    public function apply(Event $event): void
    {
        $event->wednesdays();
    }
}
