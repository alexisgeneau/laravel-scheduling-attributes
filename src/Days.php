<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * Only on the given days of the week.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Days implements Modifier
{
    /** @var list<int> */
    public array $days;

    /**
     * @param  Day|int  ...$days  Days of the week (`Day::Monday`, or 0 to 6 with Sunday as 0).
     */
    public function __construct(Day|int ...$days)
    {
        $this->days = Assert::daysOfWeek(array_values($days));
    }

    public function apply(Event $event): void
    {
        $event->days($this->days);
    }
}
