<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use SchedulingAttributes\ScheduleAttributeException;
use SchedulingAttributes\SchedulingAttributes;
use SchedulingAttributes\Tests\Fixtures\Invalid\BadTime;
use SchedulingAttributes\Tests\Fixtures\Invalid\CommandOnQueue;
use SchedulingAttributes\Tests\Fixtures\Invalid\JobInBackground;
use SchedulingAttributes\Tests\Fixtures\Invalid\JobWithArguments;
use SchedulingAttributes\Tests\Fixtures\Invalid\NotAJob;
use SchedulingAttributes\Tests\Fixtures\Invalid\OrphanModifier;

it('refuses a modifier without a frequency', function (): void {
    $this->app->make(Kernel::class)->registerCommand(new OrphanModifier);

    expect(fn () => $this->schedule()->events())
        ->toThrow(ScheduleAttributeException::class, OrphanModifier::class.' carries #[OnOneServer] but no frequency attribute');
});

it('refuses an invalid value with the attribute, the class and the reason', function (): void {
    $this->app->make(Kernel::class)->registerCommand(new BadTime);

    expect(fn () => $this->schedule()->events())
        ->toThrow(ScheduleAttributeException::class, 'Invalid #[DailyAt] on '.BadTime::class.': 25 is not a valid hour; expected 0 to 23.');
});

it('refuses a job option on a command', function (): void {
    $this->app->make(Kernel::class)->registerCommand(new CommandOnQueue);

    expect(fn () => $this->schedule()->events())
        ->toThrow(ScheduleAttributeException::class, '#[OnQueue] on '.CommandOnQueue::class.': this option only applies to jobs');
});

it('refuses a command-only option on a job', function (): void {
    SchedulingAttributes::jobs([JobInBackground::class]);

    expect(fn () => $this->schedule()->events())
        ->toThrow(ScheduleAttributeException::class, '#[RunInBackground] on '.JobInBackground::class.': the scheduler only supports this option on commands');
});

it('refuses command arguments on a job', function (): void {
    SchedulingAttributes::jobs([JobWithArguments::class]);

    expect(fn () => $this->schedule()->events())
        ->toThrow(ScheduleAttributeException::class, '#[Daily] on '.JobWithArguments::class.': a job takes no command arguments');
});

it('refuses a class that is neither a command nor a job', function (): void {
    SchedulingAttributes::jobs([NotAJob::class]);

    expect(fn () => $this->schedule()->events())
        ->toThrow(ScheduleAttributeException::class, NotAJob::class.' carries scheduling attributes but is neither a registered Artisan command nor a queued job');
});

it('refuses a wrong value as soon as the attribute is built', function (): void {
    expect(fn () => new \SchedulingAttributes\DailyAt('noon'))->toThrow(InvalidArgumentException::class, '"noon" is not a time of day')
        ->and(fn () => new \SchedulingAttributes\Cron('every day'))->toThrow(InvalidArgumentException::class, 'not a valid cron expression')
        ->and(fn () => new \SchedulingAttributes\Timezone('Mars/Olympus'))->toThrow(InvalidArgumentException::class, 'not a known timezone')
        ->and(fn () => new \SchedulingAttributes\WeeklyOn(7))->toThrow(InvalidArgumentException::class, '7 is not a valid day of the week')
        ->and(fn () => new \SchedulingAttributes\HourlyAt([15, 60]))->toThrow(InvalidArgumentException::class, '60 is not a valid minute')
        ->and(fn () => new \SchedulingAttributes\MonthlyOn(32))->toThrow(InvalidArgumentException::class, '32 is not a valid day of the month')
        ->and(fn () => new \SchedulingAttributes\YearlyOn(13))->toThrow(InvalidArgumentException::class, '13 is not a valid month')
        ->and(fn () => new \SchedulingAttributes\WithoutOverlapping(0))->toThrow(InvalidArgumentException::class, 'lock expiry must be at least 1')
        ->and(fn () => new \SchedulingAttributes\Environments)->toThrow(InvalidArgumentException::class, 'give at least one environment')
        ->and(fn () => new \SchedulingAttributes\EmailOutputTo('ops'))->toThrow(InvalidArgumentException::class, '"ops" is not an email address')
        ->and(fn () => new \SchedulingAttributes\PingBefore('not a url'))->toThrow(InvalidArgumentException::class, 'is not a URL')
        ->and(fn () => new \SchedulingAttributes\OnQueue(' '))->toThrow(InvalidArgumentException::class, 'queue cannot be blank');
});
