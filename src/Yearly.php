<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;

/**
 * The first day of every year at midnight.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Yearly implements Frequency
{
    /**
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(public array $arguments = []) {}

    public function apply(Event $event): void
    {
        $event->yearly();
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
