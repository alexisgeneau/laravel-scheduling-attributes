<?php

declare(strict_types=1);

namespace SchedulingAttributes\Console;

use Illuminate\Console\Application;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use SchedulingAttributes\Support\Registrar;
use SchedulingAttributes\Support\Registration;
use SplObjectStorage;

/**
 * Every scheduled event with where it comes from: an attribute on a class, or a call
 * to the scheduler in code. Points out the events declared both ways.
 */
final class AttributesCommand extends Command
{
    protected $signature = 'schedule:attributes';

    protected $description = 'List the scheduled events with the attribute or the code each one comes from';

    public function handle(Schedule $schedule, Registrar $registrar): int
    {
        $events = $schedule->events();

        /** @var SplObjectStorage<Event, Registration> $declared */
        $declared = new SplObjectStorage;

        foreach ($registrar->registrations() as $registration) {
            $declared[$registration->event] = $registration;
        }

        $rows = [];
        $fromCode = [];

        foreach ($events as $event) {
            $registration = $declared[$event] ?? null;
            $target = $registration?->target() ?? $this->target($event);

            $rows[] = [
                $event->getExpression(),
                $target,
                $registration !== null ? $registration->class.' '.$registration->attribute() : 'code',
                $this->nextRun($event),
            ];

            if ($registration === null) {
                $fromCode[$event->getExpression().' '.$target] = true;
            }
        }

        if ($rows === []) {
            $this->components->info('Nothing is scheduled.');

            return self::SUCCESS;
        }

        $this->table(['Expression', 'Runs', 'Source', 'Next run'], $rows);

        $duplicates = [];

        foreach ($registrar->registrations() as $registration) {
            if (isset($fromCode[$registration->event->getExpression().' '.$registration->target()])) {
                $duplicates[] = $registration;
            }
        }

        foreach ($duplicates as $registration) {
            $this->components->warn(sprintf(
                '%s is scheduled twice: %s on %s and a call in code, with the same expression. It will run twice.',
                $registration->target(),
                $registration->attribute(),
                $registration->class,
            ));
        }

        $classes = count(array_unique(array_map(static fn (Registration $registration): string => $registration->class, $registrar->registrations())));

        $this->components->info(sprintf(
            '%d scheduled %s, %d from attributes on %d %s.',
            count($rows),
            count($rows) === 1 ? 'event' : 'events',
            count($registrar->registrations()),
            $classes,
            $classes === 1 ? 'class' : 'classes',
        ));

        return $duplicates === [] ? self::SUCCESS : self::FAILURE;
    }

    private function target(Event $event): string
    {
        if ($event instanceof CallbackEvent) {
            return $event->getSummaryForDisplay();
        }

        $command = str_replace([Application::phpBinary(), Application::artisanBinary()], '', (string) $event->command);

        return trim(str_replace(['"', "'"], '', $command));
    }

    private function nextRun(Event $event): string
    {
        return Carbon::instance($event->nextRunDate())->diffForHumans();
    }
}
