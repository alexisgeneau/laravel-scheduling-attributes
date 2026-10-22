<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use SchedulingAttributes\After;
use SchedulingAttributes\Before;
use SchedulingAttributes\DailyAt;
use SchedulingAttributes\OnSuccess;
use SchedulingAttributes\Tests\Fixtures\Hooks\Allowed;
use SchedulingAttributes\Tests\Fixtures\Hooks\RecordAfter;
use SchedulingAttributes\Tests\Fixtures\Hooks\RecordBefore;
use SchedulingAttributes\Tests\Fixtures\Hooks\RecordSuccess;
use SchedulingAttributes\Tests\Fixtures\Ran;
use SchedulingAttributes\When;

#[DailyAt('05:00')]
#[When(Allowed::class)]
#[Before(RecordBefore::class), OnSuccess(RecordSuccess::class), After(RecordAfter::class)]
final class SendNightlyDigest implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Ran::$jobs[] = self::class;
    }
}
