# Changelog

## 0.2.0 - 2026-10-22

- `#[Tap]`: hands the event to an invokable class when it is registered, for anything no other attribute covers — a method added to the scheduler by a package, the `*If` variants of the pings, `thenWithOutput()` with `$onlyIfOutputExists`.
- `#[WithoutOverlapping]` takes `releaseOnTerminationSignals`, as `withoutOverlapping()` does on Laravel 13; asking for `false` on Laravel 12, which has no such option, is refused like any other unsupported method.
- `#[DaysOfMonth]` is a frequency, as `daysOfMonth()` is: at midnight on the given days, repeatable, with `arguments:`. It was listed with the day constraints, where it silently moved the time of the other frequencies to midnight. The days are given as one integer or a list: `#[DaysOfMonth([1, 15])]`.

## 0.1.0 - 2026-10-22

First release, tested with Laravel 12 and 13 on PHP 8.2 to 8.5.

- One attribute per method of Laravel's scheduler: frequencies (repeatable, with `arguments:` for the command), day and time constraints, execution options, output destinations and pings.
- Jobs: `#[OnQueue]` and `#[OnConnection]`, discovery in `app/Jobs`, `SchedulingAttributes::discoverJobsIn()` and `SchedulingAttributes::jobs()`.
- Hooks taking an invokable class: `#[When]`, `#[Skip]`, `#[Before]`, `#[After]`, `#[OnSuccess]`, `#[OnFailure]`; a `Stringable $output` parameter of `__invoke()` receives the output of the command.
- Validation of every value when the attribute is built, and a `ScheduleAttributeException` naming the class, the attribute and the reason for anything that cannot be scheduled.
- `schedule:attributes`: every scheduled event with its source, and a warning for an event declared both with an attribute and in code.
