<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Commands;

use Illuminate\Console\Command;

final class Unscheduled extends Command
{
    protected $signature = 'reports:rebuild';

    protected $description = 'A command nobody scheduled';

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
