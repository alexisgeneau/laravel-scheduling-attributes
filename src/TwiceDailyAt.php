<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Support\Assert;

/**
 * Twice a day, at the given hours and minutes past the hour.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class TwiceDailyAt implements Frequency
{
    /**
     * @param  int  $first  The first hour of the day (0 to 23).
     * @param  int  $second  The second hour of the day (0 to 23).
     * @param  int  $offset  Minutes past the hour.
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(public int $first = 1, public int $second = 13, public int $offset = 0, public array $arguments = [])
    {
        Assert::hour($this->first);
        Assert::hour($this->second);
        Assert::minute($this->offset);
    }

    public function apply(Event $event): void
    {
        $event->twiceDailyAt($this->first, $this->second, $this->offset);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
