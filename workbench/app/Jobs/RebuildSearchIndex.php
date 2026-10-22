<?php

declare(strict_types=1);

namespace Workbench\App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use SchedulingAttributes\DailyAt;
use SchedulingAttributes\OnQueue;

#[DailyAt('04:00')]
#[OnQueue('reports')]
final class RebuildSearchIndex implements ShouldQueue
{
    use Queueable;

    public function handle(): void {}
}
