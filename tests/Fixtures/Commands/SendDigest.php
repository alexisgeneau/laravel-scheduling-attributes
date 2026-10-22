<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Commands;

use Illuminate\Console\Command;
use SchedulingAttributes\Day;
use SchedulingAttributes\Environments;
use SchedulingAttributes\WeeklyOn;

#[WeeklyOn(Day::Monday, '08:00')]
#[Environments('production')]
final class SendDigest extends Command
{
    protected $signature = 'digest:send';

    protected $description = 'Send the weekly digest';

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
