<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Support\Assert;

/**
 * Every day at the given time.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class DailyAt implements Frequency
{
    /**
     * @param  string  $time  A time of day, "HH:MM".
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(public string $time, public array $arguments = [])
    {
        Assert::time($this->time);
    }

    public function apply(Event $event): void
    {
        $event->dailyAt($this->time);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
