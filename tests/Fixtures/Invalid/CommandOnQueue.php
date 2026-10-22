<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Invalid;

use Illuminate\Console\Command;
use SchedulingAttributes\Daily;
use SchedulingAttributes\OnQueue;

#[Daily]
#[OnQueue('reports')]
final class CommandOnQueue extends Command
{
    protected $signature = 'invalid:queue';

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
