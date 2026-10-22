<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Invalid;

use Illuminate\Contracts\Queue\ShouldQueue;
use SchedulingAttributes\Daily;
use SchedulingAttributes\RunInBackground;

#[Daily]
#[RunInBackground]
final class JobInBackground implements ShouldQueue
{
    public function handle(): void {}
}
