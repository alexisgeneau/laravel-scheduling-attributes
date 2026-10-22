<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;

/**
 * Every ten seconds.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class EveryTenSeconds implements Frequency
{
    /**
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(public array $arguments = []) {}

    public function apply(Event $event): void
    {
        $event->everyTenSeconds();
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
