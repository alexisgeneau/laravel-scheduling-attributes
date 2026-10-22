<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Illuminate\Container\Container;
use SchedulingAttributes\Support\Discovery;
use SchedulingAttributes\Support\Registrar;
use SchedulingAttributes\Support\Registration;

/**
 * Where the jobs are looked for. Commands need nothing: Artisan knows them all.
 */
final class SchedulingAttributes
{
    /**
     * Discover jobs in a directory. The namespace of its classes is derived from the
     * application namespace when the directory lives under the application path;
     * give it explicitly otherwise.
     */
    public static function discoverJobsIn(string $directory, ?string $namespace = null): void
    {
        self::discovery()->discoverJobsIn($directory, $namespace);
    }

    /**
     * Declare job classes one by one.
     *
     * @param  list<class-string>  $classes
     */
    public static function jobs(array $classes): void
    {
        self::discovery()->jobs($classes);
    }

    /** Do not look into app/Jobs. */
    public static function withoutDefaultJobDirectory(): void
    {
        self::discovery()->withoutDefaultDirectory();
    }

    /**
     * The events created from attributes so far, with the class and the attribute
     * each one comes from.
     *
     * @return list<Registration>
     */
    public static function registrations(): array
    {
        return Container::getInstance()->make(Registrar::class)->registrations();
    }

    private static function discovery(): Discovery
    {
        return Container::getInstance()->make(Discovery::class);
    }
}
