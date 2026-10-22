<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use SchedulingAttributes\Console\AttributesCommand;
use SchedulingAttributes\Support\AttributeSchedule;
use SchedulingAttributes\Support\Discovery;
use SchedulingAttributes\Support\Registrar;

final class SchedulingAttributesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Discovery::class);
        $this->app->singleton(Registrar::class);

        // The schedule Laravel resolves is taken over by one that reads the attributes
        // the first time its events are asked for: nothing runs until then.
        $this->app->extend(Schedule::class, fn (Schedule $schedule): Schedule => $schedule instanceof AttributeSchedule
            ? $schedule
            : AttributeSchedule::adopt($schedule, $this->app->make(Registrar::class)));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([AttributesCommand::class]);
        }
    }
}
