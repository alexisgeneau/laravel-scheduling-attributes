<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class UnscheduledJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void {}
}
