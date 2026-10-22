<?php

declare(strict_types=1);

namespace SchedulingAttributes\Support;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Stringable;
use InvalidArgumentException;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * The closures the scheduler receives for an invokable class: the class is resolved
 * by the container and invoked when the hook fires, with the output of the command
 * when its __invoke() asks for a `Stringable $output`, as a closure written by hand
 * would get it.
 */
final class Invokable
{
    /**
     * @param  class-string  $class
     * @return class-string
     */
    public static function assert(string $class): string
    {
        if (! class_exists($class)) {
            throw new InvalidArgumentException(sprintf('%s is not a class.', $class));
        }

        if (! method_exists($class, '__invoke')) {
            throw new InvalidArgumentException(sprintf('%s is not invokable; give it a public __invoke() method.', $class));
        }

        return $class;
    }

    /** @param  class-string  $class */
    public static function callback(string $class): Closure
    {
        if (self::wantsOutput($class)) {
            return static fn (Container $container, Stringable $output): mixed => $container->call($class, ['output' => $output]);
        }

        return static fn (Container $container): mixed => $container->call($class);
    }

    /** @param  class-string  $class */
    public static function condition(string $class): Closure
    {
        return static fn (Container $container): bool => (bool) $container->call($class);
    }

    /** @param  class-string  $class */
    private static function wantsOutput(string $class): bool
    {
        foreach ((new ReflectionMethod($class, '__invoke'))->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($parameter->getName() === 'output' && $type instanceof ReflectionNamedType && $type->getName() === Stringable::class) {
                return true;
            }
        }

        return false;
    }
}
