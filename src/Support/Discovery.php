<?php

declare(strict_types=1);

namespace SchedulingAttributes\Support;

use FilesystemIterator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

/**
 * Finds the classes that may carry scheduling attributes: the commands Artisan knows,
 * and the jobs found in the configured directories or declared one by one.
 */
final class Discovery
{
    /** @var array<string, string> directory => namespace of its classes */
    private array $directories = [];

    /** @var list<class-string> */
    private array $jobs = [];

    private bool $defaultDirectory = true;

    public function __construct(private readonly Application $app) {}

    /**
     * Add a directory whose classes are discovered. The namespace is derived from the
     * application namespace when the directory lives under the application path.
     */
    public function discoverJobsIn(string $directory, ?string $namespace = null): void
    {
        $this->directories[rtrim($directory, '/\\')] = $namespace !== null ? trim($namespace, '\\').'\\' : '';
    }

    /** @param  list<class-string>  $classes */
    public function jobs(array $classes): void
    {
        $this->jobs = array_values(array_unique([...$this->jobs, ...$classes]));
    }

    /** Stop looking into app/Jobs by default. */
    public function withoutDefaultDirectory(): void
    {
        $this->defaultDirectory = false;
    }

    /**
     * The commands registered with Artisan, by name.
     *
     * @return array<string, class-string>
     */
    public function commands(): array
    {
        $commands = [];

        foreach ($this->app->make(Kernel::class)->all() as $name => $command) {
            $commands[$name] = $command::class;
        }

        return $commands;
    }

    /**
     * The job classes declared or found in the directories.
     *
     * @return list<class-string>
     */
    public function jobClasses(): array
    {
        $classes = $this->jobs;

        foreach ($this->jobDirectories() as $directory => $namespace) {
            foreach ($this->classesIn($directory, $namespace) as $class) {
                $classes[] = $class;
            }
        }

        return array_values(array_unique($classes));
    }

    /** @return array<string, string> */
    private function jobDirectories(): array
    {
        $directories = $this->directories;

        if ($this->defaultDirectory && is_dir($default = $this->appPath().DIRECTORY_SEPARATOR.'Jobs')) {
            $directories[$default] ??= '';
        }

        $resolved = [];

        foreach ($directories as $directory => $namespace) {
            if (! is_dir($directory)) {
                continue;
            }

            $resolved[$directory] = $namespace !== '' ? $namespace : $this->namespaceOf($directory);
        }

        return array_filter($resolved, static fn (?string $namespace): bool => $namespace !== null);
    }

    /**
     * The namespace of a directory under the application path, from the application
     * namespace; null when it cannot be derived (then the directory is skipped).
     */
    private function namespaceOf(string $directory): ?string
    {
        $root = realpath($this->appPath());
        $path = realpath($directory);

        if ($root === false || $path === false || ! str_starts_with($path, $root.DIRECTORY_SEPARATOR)) {
            return null;
        }

        try {
            $namespace = $this->app->getNamespace();
        } catch (RuntimeException) {
            return null;
        }

        $relative = substr($path, strlen($root) + 1);

        return $namespace.str_replace(DIRECTORY_SEPARATOR, '\\', $relative).'\\';
    }

    /** The application path, as Laravel binds it: app/ unless the application moved it. */
    private function appPath(): string
    {
        return (string) $this->app->make('path');
    }

    /** @return list<class-string> */
    private function classesIn(string $directory, string $namespace): array
    {
        $files = [];

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        $classes = [];

        foreach ($files as $file) {
            $relative = substr($file, strlen($directory) + 1, -4);
            $class = $namespace.str_replace(DIRECTORY_SEPARATOR, '\\', $relative);

            if (class_exists($class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
