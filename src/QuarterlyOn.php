<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Support\Assert;

/**
 * Every quarter on the given day of its first month, at the given time.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class QuarterlyOn implements Frequency
{
    /**
     * @param  int  $dayOfQuarter  The day of the first month of the quarter (1 to 31).
     * @param  string  $time  A time of day, "HH:MM".
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(public int $dayOfQuarter = 1, public string $time = '0:0', public array $arguments = [])
    {
        Assert::dayOfMonth($this->dayOfQuarter);
        Assert::time($this->time);
    }

    public function apply(Event $event): void
    {
        $event->quarterlyOn($this->dayOfQuarter, $this->time);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
