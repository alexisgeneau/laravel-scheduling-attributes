<?php

declare(strict_types=1);

namespace SchedulingAttributes\Support;

use Closure;
use Illuminate\Console\Scheduling\Schedule;

/**
 * The application's schedule, which adds the events declared with attributes before
 * its events are read. Everything else is the schedule Laravel built.
 */
final class AttributeSchedule extends Schedule
{
    private Registrar $registrar;

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

        $registrar->attach($instance);

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function events()
    {
        $this->registrar->registerIfReady();

        return parent::events();
    }

    /**
     * {@inheritDoc}
     *
     * @return \Illuminate\Support\Collection<int, \Illuminate\Console\Scheduling\Event>
     */
    public function dueEvents($app)
    {
        $this->registrar->registerIfReady();

        return parent::dueEvents($app);
    }

    /**
     * {@inheritDoc}
     */
    public function eventsForEnvironments(array $environments): array
    {
        $this->registrar->registerIfReady();

        return parent::eventsForEnvironments($environments);
    }
}
