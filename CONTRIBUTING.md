# Contributing

Thanks for considering a contribution.

## Reporting a bug

Open an issue with the Laravel and PHP versions, the class with its attributes and what `schedule:list` or `schedule:attributes` shows. A failing Pest test is the most useful report.

## Proposing a change

1. Fork the repository and create a branch from `master`.
2. Install the dependencies: `composer install`.
3. Make the change, with tests for any new behaviour. A new attribute comes with its entry in the correspondence dataset (`tests/Feature/CorrespondenceTest.php`), which proves it produces the event of the fluent call it mirrors.
4. Make sure everything is green before opening the pull request:

```bash
composer test
composer analyse
composer lint
```

Keep pull requests focused: one change, its tests and, when it affects users, a line in `CHANGELOG.md` under *Unreleased*.

## Design rules

- One attribute per scheduler method, same name, same parameters, same order. The package defines no frequency of its own; `#[Cron]` covers what the others do not.
- Nothing runs outside the scheduler: an attribute only ever becomes calls on Laravel's `Event`, so the locks, the output and the commands behave as they do for `routes/console.php`.
- A wrong declaration fails at the first `php artisan`, with the class, the attribute and the reason. Nothing is guessed or silently skipped.
