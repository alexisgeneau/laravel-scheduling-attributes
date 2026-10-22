<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures;

use Illuminate\Support\ServiceProvider;
use SchedulingAttributes\SchedulingAttributes;
use SchedulingAttributes\Tests\Fixtures\Commands\PruneReports;
use SchedulingAttributes\Tests\Fixtures\Commands\SendDigest;
use SchedulingAttributes\Tests\Fixtures\Commands\Unscheduled;

/**
 * The "application" the tests run against: three commands and the jobs directory.
 */
final class FixtureServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([PruneReports::class, SendDigest::class, Unscheduled::class]);

        SchedulingAttributes::discoverJobsIn(__DIR__.'/Jobs', 'SchedulingAttributes\\Tests\\Fixtures\\Jobs');
    }
}
