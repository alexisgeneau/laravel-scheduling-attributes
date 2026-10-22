<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Support\Assert;

/**
 * Every four hours, at the given minutes past the hour.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class EveryFourHours implements Frequency
{
    /** @var int|list<int> */
    public int|array $offset;

    /**
     * @param  int|list<int>  $offset  Minutes past the hour, one or several.
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(int|array $offset = 0, public array $arguments = [])
    {
        $this->offset = Assert::minutes($offset);
    }

    public function apply(Event $event): void
    {
        $event->everyFourHours($this->offset);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
