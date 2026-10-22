<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Invalid;

use Illuminate\Console\Command;
use SchedulingAttributes\OnOneServer;

#[OnOneServer]
final class OrphanModifier extends Command
{
    protected $signature = 'invalid:orphan';

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
