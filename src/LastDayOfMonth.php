<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Support\Assert;

/**
 * The last day of every month, at the given time.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class LastDayOfMonth implements Frequency
{
    /**
     * @param  string  $time  A time of day, "HH:MM".
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(public string $time = '0:0', public array $arguments = [])
    {
        Assert::time($this->time);
    }

    public function apply(Event $event): void
    {
        $event->lastDayOfMonth($this->time);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
