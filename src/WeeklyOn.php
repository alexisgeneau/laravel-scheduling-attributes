<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Support\Assert;

/**
 * Every week on the given day(s), at the given time.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class WeeklyOn implements Frequency
{
    /** @var int|list<int> */
    public int|array $dayOfWeek;

    /**
     * @param  Day|int|list<Day|int>  $dayOfWeek  A day of the week, or several (`Day::Monday`, or 0 to 6 with Sunday as 0).
     * @param  string  $time  A time of day, "HH:MM".
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(Day|int|array $dayOfWeek, public string $time = '0:0', public array $arguments = [])
    {
        $this->dayOfWeek = Assert::daysOfWeek($dayOfWeek);
        Assert::time($this->time);
    }

    public function apply(Event $event): void
    {
        $event->weeklyOn($this->dayOfWeek, $this->time);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
