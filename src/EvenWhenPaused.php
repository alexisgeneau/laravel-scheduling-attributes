<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use ReflectionClass;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\UnsupportedMethod;

/**
 * Runs even while the scheduler is paused with schedule:pause.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class EvenWhenPaused implements Modifier
{
    public function apply(Event $event): void
    {
        if (! (new ReflectionClass($event))->hasMethod('evenWhenPaused')) {
            throw new UnsupportedMethod('evenWhenPaused');
        }

        $event->evenWhenPaused();
    }
}
