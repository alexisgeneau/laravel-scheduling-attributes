<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * Only between two times of day.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Between implements Modifier
{
    /**
     * @param  string  $start  The start of the interval, "HH:MM".
     * @param  string  $end  The end of the interval, "HH:MM".
     */
    public function __construct(public string $start, public string $end)
    {
        Assert::time($this->start);
        Assert::time($this->end);
    }

    public function apply(Event $event): void
    {
        $event->between($this->start, $this->end);
    }
}
