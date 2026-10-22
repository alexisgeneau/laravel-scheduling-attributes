# Changelog

## Unreleased

- One attribute per method of Laravel's scheduler: frequencies (repeatable, with `arguments:` for the command), day and time constraints, execution options, output destinations and pings.
- Jobs: `#[OnQueue]` and `#[OnConnection]`, discovery in `app/Jobs`, `SchedulingAttributes::discoverJobsIn()` and `SchedulingAttributes::jobs()`.
- Hooks taking an invokable class: `#[When]`, `#[Skip]`, `#[Before]`, `#[After]`, `#[OnSuccess]`, `#[OnFailure]`; a `Stringable $output` parameter of `__invoke()` receives the output of the command.
- Validation of every value when the attribute is built, and a `ScheduleAttributeException` naming the class, the attribute and the reason for anything that cannot be scheduled.
- `schedule:attributes`: every scheduled event with its source, and a warning for an event declared both with an attribute and in code.
