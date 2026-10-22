<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use SchedulingAttributes\SchedulingAttributes;
use Workbench\App\Console\Commands\PruneReports;

final class WorkbenchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([PruneReports::class]);

        SchedulingAttributes::discoverJobsIn(__DIR__.'/../Jobs', 'Workbench\\App\\Jobs');
    }
}
