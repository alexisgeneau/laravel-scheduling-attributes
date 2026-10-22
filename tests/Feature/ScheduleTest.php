<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schedule as ScheduleFacade;
use SchedulingAttributes\Support\AttributeSchedule;
use SchedulingAttributes\Tests\Fixtures\Commands\PruneReports;
use SchedulingAttributes\Tests\Fixtures\Hooks\Allowed;
use SchedulingAttributes\Tests\Fixtures\Jobs\RebuildSearchIndex;
use SchedulingAttributes\Tests\Fixtures\Jobs\SendNightlyDigest;
use SchedulingAttributes\Tests\Fixtures\Ran;

it('turns every frequency attribute of a command into an event with its arguments and modifiers', function (): void {
    $events = $this->eventsRunning('reports:prune');

    expect($events)->toHaveCount(2);

    [$hourly, $nightly] = $events;

    expect($hourly->getExpression())->toBe('0 * * * *')
        ->and($hourly->command)->toContain('reports:prune --days=1')
        ->and($nightly->getExpression())->toBe('0 3 * * *')
        ->and($nightly->command)->toContain('reports:prune --days=30')
        ->and($nightly->timezone)->toBe('Europe/Paris')
        ->and($nightly->withoutOverlapping)->toBeTrue()
        ->and($nightly->expiresAt)->toBe(30)
        ->and($nightly->onOneServer)->toBeTrue()
        ->and($hourly->onOneServer)->toBeTrue();
});

it('leaves commands and jobs without attributes alone', function (): void {
    expect($this->eventsRunning('reports:rebuild'))->toBe([])
        ->and($this->eventsRunning('UnscheduledJob'))->toBe([]);
});

it('schedules a discovered job on its queue', function (): void {
    $events = $this->eventsRunning(RebuildSearchIndex::class);

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(CallbackEvent::class)
        ->and($events[0]->getExpression())->toBe('0 4 * * *')
        ->and($events[0]->description)->toBe(RebuildSearchIndex::class);
});

it('keeps the events declared in code next to those declared with attributes', function (): void {
    ScheduleFacade::command('reports:rebuild')->weekly();

    $schedule = $this->schedule();

    expect($schedule)->toBeInstanceOf(AttributeSchedule::class)
        ->and($this->eventsRunning('reports:rebuild'))->toHaveCount(1)
        ->and($this->eventsRunning('reports:prune'))->toHaveCount(2)
        ->and($schedule->events())->toBe($schedule->events());
});

it('lists the attribute events with schedule:list', function (): void {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('reports:prune --days=30')
        ->expectsOutputToContain('reports:prune --days=1')
        ->expectsOutputToContain(RebuildSearchIndex::class)
        ->assertSuccessful();
});

it('runs a due job with schedule:run', function (): void {
    Ran::reset();
    config()->set('queue.default', 'sync');
    Carbon::setTestNow('2026-10-22 04:00:00');

    $this->artisan('schedule:run')->assertSuccessful();

    expect(Ran::$jobs)->toBe([RebuildSearchIndex::class])
        ->and(Ran::$hooks)->toBe([]);

    Carbon::setTestNow('2026-10-22 04:30:00');
    $this->artisan('schedule:run')->assertSuccessful();

    expect(Ran::$jobs)->toBe([RebuildSearchIndex::class]);
});

it('runs the hooks of a job around it, with the output when asked for', function (): void {
    Ran::reset();
    config()->set('queue.default', 'sync');
    Carbon::setTestNow('2026-10-22 05:00:00');

    $this->artisan('schedule:run')->assertSuccessful();

    expect(Ran::$jobs)->toBe([SendNightlyDigest::class])
        ->and(Ran::$hooks)->toBe(['before:'.config('app.name'), 'success', 'after:']);
});

it('holds a job back when its condition class says so', function (): void {
    Ran::reset();
    Allowed::$value = false;
    config()->set('queue.default', 'sync');
    Carbon::setTestNow('2026-10-22 05:00:00');

    try {
        $this->artisan('schedule:run')->assertSuccessful();
    } finally {
        Allowed::$value = true;
    }

    expect(Ran::$jobs)->toBe([])
        ->and(Ran::$hooks)->toBe([]);
});

it('holds a due event back with the same filters as the fluent calls', function (): void {
    // 03:00 in Paris: both prune events are due, but #[Between('06:00', '23:00')] holds them back.
    Carbon::setTestNow(Carbon::parse('2026-10-22 03:00:00', 'Europe/Paris'));

    $due = $this->schedule()->dueEvents($this->app);

    expect($due)->toHaveCount(2)
        ->and($due->filter->filtersPass($this->app))->toHaveCount(0);
});

it('lets a due event through its filters at the right time', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-22 08:00:00', 'Europe/Paris'));

    $runnable = $this->schedule()->dueEvents($this->app)->filter->filtersPass($this->app)->map->getExpression()->values()->all();

    expect($runnable)->toBe(['0 * * * *']);
});

it('puts the same locks on an attribute event as on its fluent twin', function (): void {
    $byAttribute = $this->eventsRunning('reports:prune')[1];
    $byCall = (new Schedule)->command(PruneReports::class, ['--days=30'])->dailyAt('03:00')->withoutOverlapping(30)->onOneServer()->timezone('Europe/Paris');

    expect($byAttribute->mutexName())->toBe($byCall->mutexName());
});
