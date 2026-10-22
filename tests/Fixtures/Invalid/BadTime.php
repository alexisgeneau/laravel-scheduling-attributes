<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Invalid;

use Illuminate\Console\Command;
use SchedulingAttributes\DailyAt;

#[DailyAt('25:00')]
final class BadTime extends Command
{
    protected $signature = 'invalid:time';

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
