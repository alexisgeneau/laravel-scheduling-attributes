<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use SchedulingAttributes\DailyAt;
use SchedulingAttributes\OnQueue;
use SchedulingAttributes\Tests\Fixtures\Ran;

#[DailyAt('04:00')]
#[OnQueue('reports')]
final class RebuildSearchIndex implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Ran::$jobs[] = self::class;
    }
}
