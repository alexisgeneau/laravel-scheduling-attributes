<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\CallbackEvent;
use SchedulingAttributes\SchedulingAttributes;
use SchedulingAttributes\Tests\Fixtures\Jobs\RebuildSearchIndex;

it('discovers the jobs of app/Jobs by default, with the application namespace', function (): void {
    $directory = $this->app->path('Jobs');
    $file = $directory.'/NightlyExport.php';

    @mkdir($directory, 0777, true);
    file_put_contents($file, <<<'PHP'
        <?php

        namespace App\Jobs;

        use Illuminate\Contracts\Queue\ShouldQueue;
        use SchedulingAttributes\DailyAt;

        #[DailyAt('02:00')]
        final class NightlyExport implements ShouldQueue
        {
            public function handle(): void {}
        }
        PHP);

    // The skeleton application has no autoloader for App\, which a real application has.
    spl_autoload_register($loader = static function (string $class) use ($file): void {
        if ($class === 'App\Jobs\NightlyExport') {
            require $file;
        }
    });

    try {
        $events = $this->eventsRunning('App\Jobs\NightlyExport');

        expect($events)->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(CallbackEvent::class)
            ->and($events[0]->getExpression())->toBe('0 2 * * *');
    } finally {
        spl_autoload_unregister($loader);
        @unlink($file);
        @rmdir($directory);
    }
});

it('can be told not to look into app/Jobs', function (): void {
    SchedulingAttributes::withoutDefaultJobDirectory();

    expect($this->eventsRunning(RebuildSearchIndex::class))->toHaveCount(1);
});

it('accepts job classes declared one by one, once', function (): void {
    SchedulingAttributes::jobs([RebuildSearchIndex::class]);
    SchedulingAttributes::jobs([RebuildSearchIndex::class]);

    expect($this->eventsRunning(RebuildSearchIndex::class))->toHaveCount(1);
});

it('registers the attribute events once, however often the events are read', function (): void {
    $schedule = $this->schedule();

    $schedule->events();
    $schedule->events();
    $this->artisan('schedule:list')->assertSuccessful();

    expect($this->eventsRunning('reports:prune'))->toHaveCount(2)
        ->and(SchedulingAttributes::registrations())->toHaveCount(5);
});
