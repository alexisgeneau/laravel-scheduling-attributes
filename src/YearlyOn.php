<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Support\Assert;

/**
 * Every year on the given month and day, at the given time.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class YearlyOn implements Frequency
{
    /**
     * @param  int  $month  The month (1 to 12).
     * @param  int  $dayOfMonth  The day of the month (1 to 31).
     * @param  string  $time  A time of day, "HH:MM".
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(public int $month = 1, public int $dayOfMonth = 1, public string $time = '0:0', public array $arguments = [])
    {
        Assert::month($this->month);
        Assert::dayOfMonth($this->dayOfMonth);
        Assert::time($this->time);
    }

    public function apply(Event $event): void
    {
        $event->yearlyOn($this->month, $this->dayOfMonth, $this->time);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
