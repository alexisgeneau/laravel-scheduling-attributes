<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Day;

/**
 * The shape of an event that matters: what the fluent API sets, nothing about the
 * objects themselves.
 *
 * @return array<string, mixed>
 */
function shape(Event $event): array
{
    $callbacks = (fn (): array => [count($this->filters), count($this->rejects), count($this->beforeCallbacks), count($this->afterCallbacks)])->call($event);

    return [
        'expression' => $event->getExpression(),
        'repeatSeconds' => $event->repeatSeconds,
        'timezone' => $event->timezone,
        'user' => $event->user,
        'environments' => $event->environments,
        'evenInMaintenanceMode' => $event->evenInMaintenanceMode,
        'evenWhenPaused' => property_exists($event, 'evenWhenPaused') ? $event->evenWhenPaused : null,
        'withoutOverlapping' => $event->withoutOverlapping,
        'expiresAt' => $event->expiresAt,
        'onOneServer' => $event->onOneServer,
        'runInBackground' => $event->runInBackground,
        'filters, rejects, before, after' => $callbacks,
        'output' => $event->output,
        'shouldAppendOutput' => $event->shouldAppendOutput,
        'description' => $event->description,
    ];
}

dataset('frequencies', [
    'Cron' => [new SchedulingAttributes\Cron('5 4 * * 1'), fn (Event $e) => $e->cron('5 4 * * 1')],
    'EverySecond' => [new SchedulingAttributes\EverySecond, fn (Event $e) => $e->everySecond()],
    'EveryTwoSeconds' => [new SchedulingAttributes\EveryTwoSeconds, fn (Event $e) => $e->everyTwoSeconds()],
    'EveryFiveSeconds' => [new SchedulingAttributes\EveryFiveSeconds, fn (Event $e) => $e->everyFiveSeconds()],
    'EveryTenSeconds' => [new SchedulingAttributes\EveryTenSeconds, fn (Event $e) => $e->everyTenSeconds()],
    'EveryFifteenSeconds' => [new SchedulingAttributes\EveryFifteenSeconds, fn (Event $e) => $e->everyFifteenSeconds()],
    'EveryTwentySeconds' => [new SchedulingAttributes\EveryTwentySeconds, fn (Event $e) => $e->everyTwentySeconds()],
    'EveryThirtySeconds' => [new SchedulingAttributes\EveryThirtySeconds, fn (Event $e) => $e->everyThirtySeconds()],
    'EveryMinute' => [new SchedulingAttributes\EveryMinute, fn (Event $e) => $e->everyMinute()],
    'EveryTwoMinutes' => [new SchedulingAttributes\EveryTwoMinutes, fn (Event $e) => $e->everyTwoMinutes()],
    'EveryThreeMinutes' => [new SchedulingAttributes\EveryThreeMinutes, fn (Event $e) => $e->everyThreeMinutes()],
    'EveryFourMinutes' => [new SchedulingAttributes\EveryFourMinutes, fn (Event $e) => $e->everyFourMinutes()],
    'EveryFiveMinutes' => [new SchedulingAttributes\EveryFiveMinutes, fn (Event $e) => $e->everyFiveMinutes()],
    'EveryTenMinutes' => [new SchedulingAttributes\EveryTenMinutes, fn (Event $e) => $e->everyTenMinutes()],
    'EveryFifteenMinutes' => [new SchedulingAttributes\EveryFifteenMinutes, fn (Event $e) => $e->everyFifteenMinutes()],
    'EveryThirtyMinutes' => [new SchedulingAttributes\EveryThirtyMinutes, fn (Event $e) => $e->everyThirtyMinutes()],
    'Hourly' => [new SchedulingAttributes\Hourly, fn (Event $e) => $e->hourly()],
    'HourlyAt' => [new SchedulingAttributes\HourlyAt([15, 45]), fn (Event $e) => $e->hourlyAt([15, 45])],
    'EveryOddHour' => [new SchedulingAttributes\EveryOddHour(10), fn (Event $e) => $e->everyOddHour(10)],
    'EveryTwoHours' => [new SchedulingAttributes\EveryTwoHours, fn (Event $e) => $e->everyTwoHours()],
    'EveryThreeHours' => [new SchedulingAttributes\EveryThreeHours(5), fn (Event $e) => $e->everyThreeHours(5)],
    'EveryFourHours' => [new SchedulingAttributes\EveryFourHours, fn (Event $e) => $e->everyFourHours()],
    'EverySixHours' => [new SchedulingAttributes\EverySixHours([0, 30]), fn (Event $e) => $e->everySixHours([0, 30])],
    'Daily' => [new SchedulingAttributes\Daily, fn (Event $e) => $e->daily()],
    'DailyAt' => [new SchedulingAttributes\DailyAt('13:30'), fn (Event $e) => $e->dailyAt('13:30')],
    'TwiceDaily' => [new SchedulingAttributes\TwiceDaily(2, 14), fn (Event $e) => $e->twiceDaily(2, 14)],
    'TwiceDailyAt' => [new SchedulingAttributes\TwiceDailyAt(2, 14, 15), fn (Event $e) => $e->twiceDailyAt(2, 14, 15)],
    'Weekly' => [new SchedulingAttributes\Weekly, fn (Event $e) => $e->weekly()],
    'WeeklyOn' => [new SchedulingAttributes\WeeklyOn(Day::Monday, '08:00'), fn (Event $e) => $e->weeklyOn(1, '08:00')],
    'WeeklyOn several days' => [new SchedulingAttributes\WeeklyOn([Day::Monday, 5], '08:00'), fn (Event $e) => $e->weeklyOn([1, 5], '08:00')],
    'Monthly' => [new SchedulingAttributes\Monthly, fn (Event $e) => $e->monthly()],
    'MonthlyOn' => [new SchedulingAttributes\MonthlyOn(4, '15:00'), fn (Event $e) => $e->monthlyOn(4, '15:00')],
    'TwiceMonthly' => [new SchedulingAttributes\TwiceMonthly(1, 16, '13:00'), fn (Event $e) => $e->twiceMonthly(1, 16, '13:00')],
    'LastDayOfMonth' => [new SchedulingAttributes\LastDayOfMonth('15:00'), fn (Event $e) => $e->lastDayOfMonth('15:00')],
    'Quarterly' => [new SchedulingAttributes\Quarterly, fn (Event $e) => $e->quarterly()],
    'QuarterlyOn' => [new SchedulingAttributes\QuarterlyOn(4, '14:00'), fn (Event $e) => $e->quarterlyOn(4, '14:00')],
    'Yearly' => [new SchedulingAttributes\Yearly, fn (Event $e) => $e->yearly()],
    'YearlyOn' => [new SchedulingAttributes\YearlyOn(6, 1, '17:00'), fn (Event $e) => $e->yearlyOn(6, 1, '17:00')],
]);

dataset('modifiers', [
    'Weekdays' => [new SchedulingAttributes\Weekdays, fn (Event $e) => $e->weekdays()],
    'Weekends' => [new SchedulingAttributes\Weekends, fn (Event $e) => $e->weekends()],
    'Sundays' => [new SchedulingAttributes\Sundays, fn (Event $e) => $e->sundays()],
    'Mondays' => [new SchedulingAttributes\Mondays, fn (Event $e) => $e->mondays()],
    'Tuesdays' => [new SchedulingAttributes\Tuesdays, fn (Event $e) => $e->tuesdays()],
    'Wednesdays' => [new SchedulingAttributes\Wednesdays, fn (Event $e) => $e->wednesdays()],
    'Thursdays' => [new SchedulingAttributes\Thursdays, fn (Event $e) => $e->thursdays()],
    'Fridays' => [new SchedulingAttributes\Fridays, fn (Event $e) => $e->fridays()],
    'Saturdays' => [new SchedulingAttributes\Saturdays, fn (Event $e) => $e->saturdays()],
    'Days' => [new SchedulingAttributes\Days(Day::Monday, Day::Wednesday), fn (Event $e) => $e->days([1, 3])],
    'DaysOfMonth' => [new SchedulingAttributes\DaysOfMonth(1, 10, 20), fn (Event $e) => $e->daysOfMonth(1, 10, 20)],
    'Between' => [new SchedulingAttributes\Between('08:00', '17:00'), fn (Event $e) => $e->between('08:00', '17:00')],
    'UnlessBetween' => [new SchedulingAttributes\UnlessBetween('23:00', '04:00'), fn (Event $e) => $e->unlessBetween('23:00', '04:00')],
    'Timezone' => [new SchedulingAttributes\Timezone('Europe/Paris'), fn (Event $e) => $e->timezone('Europe/Paris')],
    'Environments' => [new SchedulingAttributes\Environments('staging', 'production'), fn (Event $e) => $e->environments(['staging', 'production'])],
    'EvenInMaintenanceMode' => [new SchedulingAttributes\EvenInMaintenanceMode, fn (Event $e) => $e->evenInMaintenanceMode()],
    'WithoutOverlapping' => [new SchedulingAttributes\WithoutOverlapping(30), fn (Event $e) => $e->withoutOverlapping(30)],
    'OnOneServer' => [new SchedulingAttributes\OnOneServer, fn (Event $e) => $e->onOneServer()],
    'RunInBackground' => [new SchedulingAttributes\RunInBackground, fn (Event $e) => $e->runInBackground()],
    'Name' => [new SchedulingAttributes\Name('nightly'), fn (Event $e) => $e->name('nightly')],
    'User' => [new SchedulingAttributes\User('www-data'), fn (Event $e) => $e->user('www-data')],
    'SendOutputTo' => [new SchedulingAttributes\SendOutputTo('/tmp/out.log'), fn (Event $e) => $e->sendOutputTo('/tmp/out.log')],
    'AppendOutputTo' => [new SchedulingAttributes\AppendOutputTo('/tmp/out.log'), fn (Event $e) => $e->appendOutputTo('/tmp/out.log')],
    'EmailOutputTo' => [new SchedulingAttributes\EmailOutputTo('ops@example.com'), fn (Event $e) => $e->emailOutputTo(['ops@example.com'])],
    'EmailOutputOnFailure' => [new SchedulingAttributes\EmailOutputOnFailure('ops@example.com'), fn (Event $e) => $e->emailOutputOnFailure(['ops@example.com'])],
    'PingBefore' => [new SchedulingAttributes\PingBefore('https://example.com/start'), fn (Event $e) => $e->pingBefore('https://example.com/start')],
    'ThenPing' => [new SchedulingAttributes\ThenPing('https://example.com/done'), fn (Event $e) => $e->thenPing('https://example.com/done')],
    'PingOnSuccess' => [new SchedulingAttributes\PingOnSuccess('https://example.com/ok'), fn (Event $e) => $e->pingOnSuccess('https://example.com/ok')],
    'PingOnFailure' => [new SchedulingAttributes\PingOnFailure('https://example.com/ko'), fn (Event $e) => $e->pingOnFailure('https://example.com/ko')],
]);

dataset('pausable', [
    'EvenWhenPaused' => [new SchedulingAttributes\EvenWhenPaused, fn (Event $e) => $e->evenWhenPaused()],
]);

it('gives a frequency attribute the same event as its fluent call', function (Frequency $attribute, Closure $fluent): void {
    $schedule = new Schedule;

    $attribute->apply($byAttribute = $schedule->command('reports:prune'));
    $fluent($byCall = $schedule->command('reports:prune'));

    expect(shape($byAttribute))->toBe(shape($byCall));
})->with('frequencies');

it('gives a modifier attribute the same event as its fluent call', function (Modifier $attribute, Closure $fluent): void {
    $schedule = new Schedule;

    $attribute->apply($byAttribute = $schedule->command('reports:prune')->daily());
    $fluent($byCall = $schedule->command('reports:prune')->daily());

    expect(shape($byAttribute))->toBe(shape($byCall))
        ->and(shape($byAttribute))->not->toBe(shape($schedule->command('reports:prune')->daily()));
})->with('modifiers');

it('gives the pause modifier the same event as its fluent call, where Laravel has one', function (Modifier $attribute, Closure $fluent): void {
    $schedule = new Schedule;

    $attribute->apply($byAttribute = $schedule->command('reports:prune')->daily());
    $fluent($byCall = $schedule->command('reports:prune')->daily());

    expect(shape($byAttribute))->toBe(shape($byCall));
})->with('pausable')->skip(! method_exists(Event::class, 'evenWhenPaused'), 'This version of Laravel has no schedule:pause.');
