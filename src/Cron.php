<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Support\Assert;

/**
 * A custom cron expression, as `->cron()` takes it.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Cron implements Frequency
{
    /**
     * @param  string  $expression  A five-field cron expression.
     * @param  list<string>  $arguments  Arguments and options for the command, as `Schedule::command()` takes them.
     */
    public function __construct(public string $expression, public array $arguments = [])
    {
        Assert::cron($this->expression);
    }

    public function apply(Event $event): void
    {
        $event->cron($this->expression);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
