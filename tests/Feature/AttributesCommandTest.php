<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use SchedulingAttributes\Tests\Fixtures\Commands\PruneReports;
use SchedulingAttributes\Tests\Fixtures\Commands\SendDigest;
use SchedulingAttributes\Tests\Fixtures\Jobs\RebuildSearchIndex;
use SchedulingAttributes\Tests\Fixtures\Jobs\SendNightlyDigest;

it('lists every event with where it comes from', function (): void {
    Schedule::command('reports:rebuild')->weekly();

    $exit = Artisan::call('schedule:attributes');
    $output = Artisan::output();

    expect($exit)->toBe(0)
        ->and($output)->toContain('0 * * * * Europe/Paris | reports:prune --days=1 ', PruneReports::class.' #[Hourly]')
        ->and($output)->toContain('0 3 * * * Europe/Paris | reports:prune --days=30 ', PruneReports::class.' #[DailyAt]')
        ->and($output)->toContain('0 8 * * 1              | digest:send ', SendDigest::class.' #[WeeklyOn]')
        ->and($output)->toContain('0 4 * * *              | '.RebuildSearchIndex::class.' ', RebuildSearchIndex::class.' #[DailyAt]')
        ->and($output)->toContain('0 5 * * *              | '.SendNightlyDigest::class.' ', SendNightlyDigest::class.' #[DailyAt]')
        ->and($output)->toContain('0 0 * * 0              | reports:rebuild ', '| code ')
        ->and($output)->toContain('6 scheduled events, 5 from attributes on 4 classes.');
});

it('points out an event scheduled both with an attribute and in code', function (): void {
    Schedule::command('reports:prune', ['--days=30'])->dailyAt('03:00');

    $exit = Artisan::call('schedule:attributes');

    expect($exit)->toBe(1)
        ->and(Artisan::output())->toContain('reports:prune --days=30 is scheduled twice: #[DailyAt] on '.PruneReports::class.' and a call in code, with the same expression. It will run twice.');
});
