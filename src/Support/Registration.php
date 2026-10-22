<?php

declare(strict_types=1);

namespace SchedulingAttributes\Support;

use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Frequency;

/**
 * One event created from one frequency attribute, with where it came from.
 */
final readonly class Registration
{
    /**
     * @param  class-string  $class
     * @param  string|null  $command  The command name, null for a job.
     */
    public function __construct(
        public string $class,
        public ?string $command,
        public Frequency $frequency,
        public Event $event,
    ) {}

    public function isJob(): bool
    {
        return $this->command === null;
    }

    /** The attribute as it reads in the source, for instance `#[DailyAt]`. */
    public function attribute(): string
    {
        return '#['.class_basename($this->frequency).']';
    }

    /** What the event runs: the command with its arguments, or the job class. */
    public function target(): string
    {
        if ($this->command === null) {
            return $this->class;
        }

        return trim($this->command.' '.implode(' ', $this->frequency->arguments()));
    }
}
