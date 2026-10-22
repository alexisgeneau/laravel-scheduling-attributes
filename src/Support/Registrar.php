<?php

declare(strict_types=1);

namespace SchedulingAttributes\Support;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use ReflectionAttribute;
use ReflectionClass;
use SchedulingAttributes\Contracts\CommandOnly;
use SchedulingAttributes\Contracts\Frequency;
use SchedulingAttributes\Contracts\JobOption;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\OnConnection;
use SchedulingAttributes\OnQueue;
use SchedulingAttributes\ScheduleAttributeException;
use Throwable;

/**
 * Reads the scheduling attributes of every command and job and puts the events they
 * describe on the schedule, exactly as the fluent calls would.
 */
final class Registrar
{
    /** @var list<Registration> */
    private array $registrations = [];

    public function __construct(
        private readonly Application $app,
        private readonly Discovery $discovery,
    ) {}

    /**
     * Whether the commands can be enumerated now. During the console kernel's own
     * bootstrap the command list is still being built, and asking Artisan for it
     * would load the console routes a second time.
     */
    public function ready(): bool
    {
        if (! $this->app->runningInConsole()) {
            return true;
        }

        $kernel = $this->app->make(Kernel::class);
        $reflection = new ReflectionClass($kernel);

        if (! $reflection->hasProperty('commandsLoaded')) {
            return true;
        }

        return $reflection->getProperty('commandsLoaded')->getValue($kernel) === true;
    }

    public function register(Schedule $schedule): void
    {
        $seen = [];

        foreach ($this->discovery->commands() as $name => $class) {
            if (isset($seen[$class])) {
                continue;
            }

            $seen[$class] = true;
            $this->registerClass($schedule, $class, $name);
        }

        foreach ($this->discovery->jobClasses() as $class) {
            if (! isset($seen[$class])) {
                $seen[$class] = true;
                $this->registerClass($schedule, $class, null);
            }
        }
    }

    /** @return list<Registration> */
    public function registrations(): array
    {
        return $this->registrations;
    }

    /** @param  class-string  $class */
    private function registerClass(Schedule $schedule, string $class, ?string $command): void
    {
        $reflection = new ReflectionClass($class);

        $frequencies = $this->instances($reflection, Frequency::class);
        $modifiers = $this->instances($reflection, Modifier::class);
        $options = $this->instances($reflection, JobOption::class);

        if ($frequencies === [] && $modifiers === [] && $options === []) {
            return;
        }

        if ($frequencies === []) {
            throw ScheduleAttributeException::withoutFrequency($class, ($modifiers[0] ?? $options[0])::class);
        }

        if ($command === null) {
            $this->assertJob($reflection, $frequencies, $modifiers);
        } elseif ($options !== []) {
            throw ScheduleAttributeException::jobOnly($class, $options[0]::class);
        }

        [$queue, $connection] = $this->jobOptions($options);

        foreach ($frequencies as $frequency) {
            $event = $command !== null
                ? $schedule->command($command, $frequency->arguments())
                : $schedule->job($class, $queue, $connection);

            $this->apply($frequency, $event, $class);

            foreach ($modifiers as $modifier) {
                $this->apply($modifier, $event, $class);
            }

            $this->registrations[] = new Registration($class, $command, $frequency, $event);
        }
    }

    /**
     * @param  ReflectionClass<object>  $reflection
     * @param  list<Frequency>  $frequencies
     * @param  list<Modifier>  $modifiers
     */
    private function assertJob(ReflectionClass $reflection, array $frequencies, array $modifiers): void
    {
        $class = $reflection->getName();

        if (! $reflection->implementsInterface(ShouldQueue::class) && ! $reflection->hasMethod('handle') && ! $reflection->hasMethod('__invoke')) {
            throw ScheduleAttributeException::notSchedulable($class);
        }

        foreach ($frequencies as $frequency) {
            if ($frequency->arguments() !== []) {
                throw ScheduleAttributeException::argumentsOnJob($class, $frequency::class);
            }
        }

        foreach ($modifiers as $modifier) {
            if ($modifier instanceof CommandOnly) {
                throw ScheduleAttributeException::commandOnly($class, $modifier::class);
            }
        }
    }

    /**
     * @param  list<JobOption>  $options
     * @return array{0: string|null, 1: string|null}
     */
    private function jobOptions(array $options): array
    {
        $queue = $connection = null;

        foreach ($options as $option) {
            if ($option instanceof OnQueue) {
                $queue = $option->queue;
            } elseif ($option instanceof OnConnection) {
                $connection = $option->connection;
            }
        }

        return [$queue, $connection];
    }

    private function apply(Frequency|Modifier $attribute, Event $event, string $class): void
    {
        try {
            $attribute->apply($event);
        } catch (UnsupportedMethod $exception) {
            throw ScheduleAttributeException::unsupported($class, $attribute::class, $exception->method);
        }
    }

    /**
     * @template T of object
     *
     * @param  ReflectionClass<object>  $reflection
     * @param  class-string<T>  $interface
     * @return list<T>
     */
    private function instances(ReflectionClass $reflection, string $interface): array
    {
        $instances = [];

        foreach ($reflection->getAttributes($interface, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            try {
                $instances[] = $attribute->newInstance();
            } catch (Throwable $exception) {
                throw ScheduleAttributeException::invalid($reflection->getName(), $attribute->getName(), $exception);
            }
        }

        return $instances;
    }
}
