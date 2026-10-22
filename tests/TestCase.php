<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Orchestra\Testbench\TestCase as Orchestra;
use SchedulingAttributes\SchedulingAttributesServiceProvider;
use SchedulingAttributes\Tests\Fixtures\FixtureServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @return list<class-string> */
    protected function getPackageProviders($app): array
    {
        return [SchedulingAttributesServiceProvider::class, FixtureServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('app.timezone', 'UTC');
    }

    /** The schedule as a command receives it. */
    protected function schedule(): Schedule
    {
        return $this->app->make(Schedule::class);
    }

    /** @return list<Event> */
    protected function eventsRunning(string $target): array
    {
        return array_values(array_filter($this->schedule()->events(), function (Event $event) use ($target): bool {
            $command = $event->command ?? $event->getSummaryForDisplay();

            return str_contains((string) $command, $target);
        }));
    }
}
