<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * Only on the given days of the month.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class DaysOfMonth implements Modifier
{
    /** @var list<int> */
    public array $days;

    /**
     * @param  int  ...$days  Days of the month (1 to 31).
     */
    public function __construct(int ...$days)
    {
        $this->days = array_map(Assert::dayOfMonth(...), Assert::nonEmptyList(array_values($days), 'day of the month'));
    }

    public function apply(Event $event): void
    {
        $event->daysOfMonth(...$this->days);
    }
}
