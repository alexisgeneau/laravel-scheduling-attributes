# Laravel scheduling attributes

Schedule Artisan commands and jobs where they are written: PHP attributes on the class, registered in Laravel's own scheduler.

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
| Frequency | `Cron`, `EverySecond` … `EveryThirtySeconds`, `EveryMinute` … `EveryThirtyMinutes`, `Hourly`, `HourlyAt`, `EveryOddHour`, `EveryTwoHours` … `EverySixHours`, `Daily`, `DailyAt`, `TwiceDaily`, `TwiceDailyAt`, `Weekly`, `WeeklyOn`, `Monthly`, `MonthlyOn`, `TwiceMonthly`, `LastDayOfMonth`, `Quarterly`, `QuarterlyOn`, `Yearly`, `YearlyOn` | repeatable, one event each, `arguments:` optional |
| Days and times | `Weekdays`, `Weekends`, `Mondays` … `Sundays`, `Days`, `DaysOfMonth`, `Between`, `UnlessBetween`, `Timezone`, `Environments`, `EvenInMaintenanceMode`, `EvenWhenPaused` | class |
| Execution | `WithoutOverlapping`, `OnOneServer`, `RunInBackground`, `Name`, `User` | class |
| Output | `SendOutputTo`, `AppendOutputTo`, `EmailOutputTo`, `EmailOutputOnFailure` | class, commands only |
| Pings | `PingBefore`, `ThenPing`, `PingOnSuccess`, `PingOnFailure` | class |
| Jobs | `OnQueue`, `OnConnection` | class, jobs only |

Days of the week take the `SchedulingAttributes\Day` enum or Laravel's integers (Sunday is 0); times are `HH:MM`; several minutes past the hour, days or addresses are passed as a list or as variadic arguments (`#[Days(Day::Monday, Day::Friday)]`, `#[Environments('staging', 'production')]`). Every value is checked when the attribute is built: a wrong one is refused with the attribute, the class and the reason.

### What stays in `routes/console.php`

An attribute cannot hold a closure, so `when`, `skip`, `before`, `after`, `onSuccess`, `onFailure`, `then` and the `*If` variants of the pings stay in code, as do `Schedule::call()` and `Schedule::exec()`. Give the event a `#[Name]` and you can still reach it from `routes/console.php` to add a hook.

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

A wrong declaration is reported as soon as the schedule is read, which in practice is the first `php artisan` of the day in development. The message names the class, the attribute and the reason, and every command fails until it is fixed, exactly as with an error in `routes/console.php`:

```
Invalid #[DailyAt] on App\Console\Commands\PruneReports: 25 is not a valid hour; expected 0 to 23.
App\Console\Commands\Backup carries #[OnOneServer] but no frequency attribute such as #[DailyAt] or #[Cron], so there is nothing to schedule.
#[RunInBackground] on App\Jobs\RebuildSearchIndex: the scheduler only supports this option on commands, not on jobs.
```

Attributes are read on the concrete class only; nothing is inherited from an abstract parent, so reading a class is enough to know when it runs.

## `schedule:attributes`

Lists every scheduled event with where it comes from, and warns when the same command is scheduled both with an attribute and in code with the same expression — then it exits with a failure code, so it can run in CI.

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

Laravel resolves one `Schedule` for the whole process. The package takes it over with a subclass whose `events()` method, the first time it is called, asks Artisan for the registered commands, scans the job directories, reads the attributes by reflection and registers the events; `schedule:list`, `schedule:run` and everything else read events through that method. Nothing happens in an HTTP request or a queue worker until something asks for the schedule, and a hundred attributed classes register in about 4 ms, once per process — the time `schedule:run` spends booting the application each minute dwarfs it.

## Development

```bash
composer install
composer test      # Pest
composer analyse   # PHPStan (Larastan, level 6)
composer lint      # Pint
```

The suite compares, for every attribute, the event it produces with the event the equivalent fluent call produces, then exercises `schedule:list`, `schedule:run`, the job discovery, the refusals and `schedule:attributes` against a small application of fixtures.

## License

MIT.
