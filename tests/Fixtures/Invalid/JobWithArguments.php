<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Invalid;

use Illuminate\Contracts\Queue\ShouldQueue;
use SchedulingAttributes\Daily;

#[Daily(arguments: ['--force'])]
final class JobWithArguments implements ShouldQueue
{
    public function handle(): void {}
}
