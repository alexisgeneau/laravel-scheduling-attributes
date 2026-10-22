<?php

declare(strict_types=1);

namespace SchedulingAttributes\Support;

use Closure;
use Illuminate\Console\Scheduling\Schedule;

/**
 * The application's schedule, which adds the events declared with attributes the
 * first time its events are read. Everything else is the schedule Laravel built.
 */
final class AttributeSchedule extends Schedule
{
    private Registrar $registrar;

    private bool $registered = false;

    private bool $registering = false;

    /** Takes over the schedule Laravel resolved, with everything it already holds. */
    public static function adopt(Schedule $schedule, Registrar $registrar): self
    {
        $instance = new self;
        $instance->registrar = $registrar;

        $state = Closure::bind(static fn (Schedule $schedule): array => get_object_vars($schedule), null, Schedule::class)($schedule);

        Closure::bind(static function (Schedule $instance, array $state): void {
            foreach ($state as $property => $value) {
                $instance->{$property} = $value;
            }
        }, null, Schedule::class)($instance, $state);

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function events()
    {
        if (! $this->registered && ! $this->registering && $this->registrar->ready()) {
            $this->registering = true;

            try {
                $this->registrar->register($this);
            } finally {
                $this->registered = true;
                $this->registering = false;
            }
        }

        return parent::events();
    }
}
