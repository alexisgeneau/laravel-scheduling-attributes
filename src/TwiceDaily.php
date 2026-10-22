<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Support\Assert;

/**
 * Twice a day, at the given hours.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class TwiceDaily implements Frequency
{
    /**
     * @param  int  $first  The first hour of the day (0 to 23).
     * @param  int  $second  The second hour of the day (0 to 23).
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(public int $first = 1, public int $second = 13, public array $arguments = [])
    {
        Assert::hour($this->first);
        Assert::hour($this->second);
    }

    public function apply(Event $event): void
    {
        $event->twiceDaily($this->first, $this->second);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
