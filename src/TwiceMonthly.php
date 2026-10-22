<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Support\Assert;

/**
 * Twice a month, on the given days at the given time.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class TwiceMonthly implements Frequency
{
    /**
     * @param  int  $first  The first day of the month (1 to 31).
     * @param  int  $second  The second day of the month (1 to 31).
     * @param  string  $time  A time of day, "HH:MM".
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(public int $first = 1, public int $second = 16, public string $time = '0:0', public array $arguments = [])
    {
        Assert::dayOfMonth($this->first);
        Assert::dayOfMonth($this->second);
        Assert::time($this->time);
    }

    public function apply(Event $event): void
    {
        $event->twiceMonthly($this->first, $this->second, $this->time);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
