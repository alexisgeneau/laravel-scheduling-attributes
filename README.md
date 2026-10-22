# Laravel scheduling attributes

Schedule Artisan commands and jobs where they are written: PHP attributes on the class, registered in Laravel's own scheduler.

> **Status:** 0.2.0, tested with Laravel 12 and 13 on PHP 8.2 to 8.5. Until 1.0, a minor version may still change the `SchedulingAttributes` discovery methods, the wording of the refusals or the output of `schedule:attributes`; the changelog says so when it happens.

```php
use Illuminate\Console\Command;
use SchedulingAttributes\{Between, DailyAt, Hourly, OnOneServer, Timezone, WithoutOverlapping};

#[Hourly(arguments: ['--days=1'])]
#[DailyAt('03:00', arguments: ['--days=30'])]
#[Between('06:00', '23:00')]
#[WithoutOverlapping(30), OnOneServer, Timezone('Europe/Paris')]
final class PruneReports extends Command
{
    protected $signature = 'reports:prune {--days=7}';
}
```

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use SchedulingAttributes\{DailyAt, OnQueue};

#[DailyAt('04:00')]
#[OnQueue('reports')]
final class RebuildSearchIndex implements ShouldQueue
{
    public function handle(): void { /* ... */ }
}
```

That is the whole feature. `php artisan schedule:list` shows two `reports:prune` lines with their arguments and one `App\Jobs\RebuildSearchIndex` line, `schedule:run` runs them, `withoutOverlapping` and `onOneServer` take the same locks, exactly as if the three lines stood in `routes/console.php`.

## Why

The *when* of a command lives far from the command. To know how often `reports:prune` runs you open `routes/console.php`; when you delete the command its line stays behind and `schedule:run` fails every night; with forty scheduled commands the file turns into an inventory nobody reads. Yet `withoutOverlapping`, `onOneServer`, `runInBackground` or the timezone describe the command itself, the way `tries` and `timeout` describe a job. Laravel 13 lets a command carry its name and description as attributes; this package adds the *when*.

## Requirements

- PHP 8.2 or newer
- Laravel 12 or 13

## Installation

```bash
composer require alexisgeneau/laravel-scheduling-attributes
```

Nothing to publish, configure or add: the service provider is discovered, and your attributes take effect at the next `php artisan`.

## What does not change

The package never runs anything itself. The first time the schedule's events are read, it turns the attributes into calls on Laravel's `Schedule`, the same calls you would write by hand, and steps aside. `schedule:list`, `schedule:run`, `schedule:work`, `schedule:test`, `schedule:interrupt`, `schedule:clear-cache`, the overlap and single-server locks, the output capture, the pings, the maintenance mode and `schedule:pause` behave as they always did, and so do the tools built on them. `routes/console.php` keeps working next to the attributes; an event declared there and an event declared with an attribute are indistinguishable once on the schedule.

## The rule

**One attribute per method of the scheduler, same name in PascalCase, same parameters in the same order.** `->dailyAt('03:00')` becomes `#[DailyAt('03:00')]`, `->withoutOverlapping(10)` becomes `#[WithoutOverlapping(10)]`, `->weeklyOn(1, '8:00')` becomes `#[WeeklyOn(Day::Monday, '08:00')]`. The [scheduler documentation](https://laravel.com/docs/13.x/scheduling) stays the reference; there is nothing else to learn.

Frequency attributes are **repeatable**: each one creates an event, a class may carry several, and each accepts `arguments:` with the options and arguments to pass to the command for that event. Every other attribute applies to the class, so to all of its events.

| Group | Attributes | Scope |
| --- | --- | --- |
| Frequency | `Cron`, `EverySecond` … `EveryThirtySeconds`, `EveryMinute` … `EveryThirtyMinutes`, `Hourly`, `HourlyAt`, `EveryOddHour`, `EveryTwoHours` … `EverySixHours`, `Daily`, `DailyAt`, `TwiceDaily`, `TwiceDailyAt`, `Weekly`, `WeeklyOn`, `Monthly`, `MonthlyOn`, `TwiceMonthly`, `DaysOfMonth`, `LastDayOfMonth`, `Quarterly`, `QuarterlyOn`, `Yearly`, `YearlyOn` | repeatable, one event each, `arguments:` optional |
| Days and times | `Weekdays`, `Weekends`, `Mondays` … `Sundays`, `Days`, `Between`, `UnlessBetween`, `Timezone`, `Environments`, `EvenInMaintenanceMode`, `EvenWhenPaused` | class |
| Execution | `WithoutOverlapping`, `OnOneServer`, `RunInBackground`, `Name`, `User` | class |
| Output | `SendOutputTo`, `AppendOutputTo`, `EmailOutputTo`, `EmailOutputOnFailure` | class, commands only |
| Pings | `PingBefore`, `ThenPing`, `PingOnSuccess`, `PingOnFailure` | class |
| Hooks | `When`, `Skip`, `Before`, `After`, `OnSuccess`, `OnFailure`, `Tap` | class, repeatable, take an invokable class |
| Jobs | `OnQueue`, `OnConnection` | class, jobs only |

Days of the week take the `SchedulingAttributes\Day` enum or Laravel's integers (Sunday is 0); times are `HH:MM`; several minutes past the hour, days or addresses are passed as a list or as variadic arguments (`#[Days(Day::Monday, Day::Friday)]`, `#[Environments('staging', 'production')]`). Every value is checked when the attribute is built: a wrong one is refused with the attribute, the class and the reason. The scheduler's aliases have no attribute of their own: `at()` is `DailyAt`, `then()` is `After`, `description()` is `Name`, `emailWrittenOutputTo()` is `EmailOutputTo`.

### Hooks

An attribute cannot hold a closure, so the hooks take the class of an invokable instead. The container resolves it when the hook fires, injects its constructor dependencies, and hands its `__invoke()` the output of the command when it asks for a `Stringable $output` parameter — the same rule Laravel applies to the closures of `after()`, `onSuccess()` and `onFailure()`:

```php
#[DailyAt('02:00')]
#[When(IsBusinessDay::class)]
#[OnFailure(NotifyOps::class)]
final class ReconcilePayments extends Command { /* ... */ }

final class NotifyOps
{
    public function __construct(private readonly Notifier $notifier) {}

    public function __invoke(Stringable $output): void
    {
        $this->notifier->send('Payment reconciliation failed', $output->toString());
    }
}
```

`When` and `Skip` take the verdict of `__invoke()`; `Before`, `After`, `OnSuccess` and `OnFailure` run it. All six are repeatable.

### Everything else: `Tap`

`Event::tap()` has its attribute too. `#[Tap(MonitorWithSentry::class)]` hands the event to the invokable when it is registered, so anything the scheduler can do that no other attribute covers stays next to the class: a method added to the scheduler by a package, the `*If` variants of the pings, `thenWithOutput()` with `$onlyIfOutputExists`.

```php
#[Daily, Tap(MonitorWithSentry::class)]
final class ReconcilePayments extends Command { /* ... */ }

final class MonitorWithSentry
{
    public function __invoke(Event $event): void
    {
        $event->sentryMonitor();
        $event->pingOnFailureIf(app()->isProduction(), 'https://example.com/ko');
    }
}
```

Only `Schedule::call()` and `Schedule::exec()` stay in `routes/console.php`: there is no class to carry an attribute. They run next to the attributes, on the same schedule.

## Jobs

Jobs found in `app/Jobs` are read like commands: a job carrying a frequency attribute is scheduled with `Schedule::job()`, pushed on the queue and connection of its `#[OnQueue]` and `#[OnConnection]` attributes, or on its own `$queue` and `$connection` properties otherwise. The job is instantiated by the container when the event runs, so it takes no constructor arguments beyond dependencies the container can resolve, and a frequency attribute on a job takes no `arguments:`.

Jobs living elsewhere are declared in a service provider:

```php
use SchedulingAttributes\SchedulingAttributes;

SchedulingAttributes::discoverJobsIn(app_path('Domain/Billing/Jobs'));             // under app/, the namespace is derived
SchedulingAttributes::discoverJobsIn(base_path('modules/Search/Jobs'), 'Modules\\Search\\Jobs');
SchedulingAttributes::jobs([RebuildSearchIndex::class]);                              // one by one
SchedulingAttributes::withoutDefaultJobDirectory();                                   // if app/Jobs should not be read
```

## Errors

A wrong declaration is reported as soon as the schedule is read: `schedule:list`, `schedule:run`, `schedule:work`, `schedule:test` and `schedule:attributes` stop with a message naming the class, the attribute and the reason, and nothing is scheduled until it is fixed. The other commands are not affected.

```
Invalid #[DailyAt] on App\Console\Commands\PruneReports: 25 is not a valid hour; expected 0 to 23.
App\Console\Commands\Backup carries #[OnOneServer] but no frequency attribute such as #[DailyAt] or #[Cron], so there is nothing to schedule.
#[RunInBackground] on App\Jobs\RebuildSearchIndex: the scheduler only supports this option on commands, not on jobs.
```

Attributes are read on the concrete class only; nothing is inherited from an abstract parent, so reading a class is enough to know when it runs.

## `schedule:attributes`

Lists every scheduled event with where it comes from (the expression carries its timezone when it differs from the application's), and warns when the same command is scheduled both with an attribute and in code with the same expression — then it exits with a failure code, so it can run in CI.

```
+------------+----------------------------+----------------------------------------------+---------------------+
| Expression | Runs                       | Source                                       | Next run            |
+------------+----------------------------+----------------------------------------------+---------------------+
| 0 * * * *  | reports:prune --days=1     | App\Console\Commands\PruneReports #[Hourly]  | 10 minutes from now |
| 0 3 * * *  | reports:prune --days=30    | App\Console\Commands\PruneReports #[DailyAt] | 13 hours from now   |
| 0 4 * * *  | App\Jobs\RebuildSearchIndex| App\Jobs\RebuildSearchIndex #[DailyAt]       | 14 hours from now   |
| 0 0 * * 0  | reports:rebuild            | code                                         | 2 days from now     |
+------------+----------------------------+----------------------------------------------+---------------------+

 INFO  4 scheduled events, 3 from attributes on 2 classes.
```

## How it works

Laravel resolves one `Schedule` for the whole process. The package takes it over and, the first time a command receives it or its events are read once Artisan knows the application's commands, asks Artisan for those commands, scans the job directories, reads the attributes by reflection and registers the events. `schedule:list`, `schedule:run` and everything else then see them like any other event. Nothing happens in an HTTP request or a queue worker until something asks for the schedule, and a hundred attributed classes register in about 5 ms, once per process — the time `schedule:run` spends booting the application each minute dwarfs it.

## Development

```bash
composer install
composer test      # Pest
composer analyse   # PHPStan (Larastan, level 6)
composer lint      # Pint
```

The suite compares, for every attribute, the event it produces with the event the equivalent fluent call produces, then exercises `schedule:list`, `schedule:run`, the job discovery, the refusals and `schedule:attributes` against a small application of fixtures. `vendor/bin/testbench schedule:attributes` (or `schedule:list`, `schedule:run`) runs the real commands against the `workbench/` application, where a command and a job carry attributes next to a `routes/console.php` entry.

## License

MIT.
