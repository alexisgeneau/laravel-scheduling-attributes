<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;

it('registers a hundred attributed commands in a few milliseconds', function (): void {
    $kernel = $this->app->make(Kernel::class);

    for ($i = 0; $i < 100; $i++) {
        eval(<<<PHP
            namespace SchedulingAttributes\\Tests\\Generated;

            #[\\SchedulingAttributes\\DailyAt('03:00')]
            #[\\SchedulingAttributes\\WithoutOverlapping, \\SchedulingAttributes\\OnOneServer]
            final class Generated{$i} extends \\Illuminate\\Console\\Command
            {
                protected \$signature = 'generated:{$i}';

                public function handle(): int
                {
                    return self::SUCCESS;
                }
            }
            PHP);

        $class = "SchedulingAttributes\\Tests\\Generated\\Generated{$i}";
        $kernel->registerCommand(new $class);
    }

    // Resolving the schedule from a command registers the attributes: that is what is timed.
    $start = hrtime(true);
    $events = $this->app->make(Schedule::class)->events();
    $elapsed = (hrtime(true) - $start) / 1e6;

    expect(count(array_filter($events, static fn ($event): bool => str_contains((string) $event->command, 'generated:'))))->toBe(100)
        ->and($elapsed)->toBeLessThan(20.0);

    fwrite(STDERR, sprintf("\n  a hundred attributed commands registered in %.1f ms\n", $elapsed));
});
