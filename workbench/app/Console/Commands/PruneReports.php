<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use SchedulingAttributes\Between;
use SchedulingAttributes\DailyAt;
use SchedulingAttributes\Hourly;
use SchedulingAttributes\OnOneServer;
use SchedulingAttributes\Timezone;
use SchedulingAttributes\WithoutOverlapping;

#[Hourly(arguments: ['--days=1'])]
#[DailyAt('03:00', arguments: ['--days=30'])]
#[Between('06:00', '23:00')]
#[WithoutOverlapping(30), OnOneServer, Timezone('Europe/Paris')]
final class PruneReports extends Command
{
    protected $signature = 'reports:prune {--days=7}';

    protected $description = 'Delete reports older than the retention period';

    public function handle(): int
    {
        $this->info(sprintf('Pruned the reports older than %d days.', (int) $this->option('days')));

        return self::SUCCESS;
    }
}
