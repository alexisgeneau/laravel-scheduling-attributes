<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Support\Assert;

/**
 * At midnight on the given days of the month.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class DaysOfMonth implements Frequency
{
    /** @var list<int> */
    public array $days;

    /**
     * @param  int|list<int>  $days  One or several days of the month (1 to 31).
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(int|array $days, public array $arguments = [])
    {
        $this->days = array_map(Assert::dayOfMonth(...), Assert::nonEmptyList(is_array($days) ? $days : [$days], 'day of the month'));
    }

    public function apply(Event $event): void
    {
        $event->daysOfMonth(...$this->days);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
