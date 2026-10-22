<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use RuntimeException;
use Throwable;

/**
 * A scheduling attribute that cannot be turned into an event. Raised while Artisan
 * starts, so a wrong declaration is seen at the first `php artisan` in development.
 */
final class ScheduleAttributeException extends RuntimeException
{
    public static function invalid(string $class, string $attribute, Throwable $cause): self
    {
        return new self(sprintf('Invalid #[%s] on %s: %s', class_basename($attribute), $class, $cause->getMessage()), 0, $cause);
    }

    public static function withoutFrequency(string $class, string $attribute): self
    {
        return new self(sprintf('%s carries #[%s] but no frequency attribute such as #[DailyAt] or #[Cron], so there is nothing to schedule.', $class, class_basename($attribute)));
    }

    public static function notSchedulable(string $class): self
    {
        return new self(sprintf('%s carries scheduling attributes but is neither a registered Artisan command nor a queued job (ShouldQueue).', $class));
    }

    public static function commandOnly(string $class, string $attribute): self
    {
        return new self(sprintf('#[%s] on %s: the scheduler only supports this option on commands, not on jobs.', class_basename($attribute), $class));
    }

    public static function jobOnly(string $class, string $attribute): self
    {
        return new self(sprintf('#[%s] on %s: this option only applies to jobs; a command runs on no queue.', class_basename($attribute), $class));
    }

    public static function argumentsOnJob(string $class, string $attribute): self
    {
        return new self(sprintf('#[%s] on %s: a job takes no command arguments; give it constructor dependencies the container can resolve instead.', class_basename($attribute), $class));
    }

    public static function unsupported(string $class, string $attribute, string $method): self
    {
        return new self(sprintf('#[%s] on %s: this version of Laravel has no Schedule::%s() method.', class_basename($attribute), $class, $method));
    }

    public static function notFound(string $class): self
    {
        return new self(sprintf('%s was declared as a scheduled job but the class does not exist.', $class));
    }
}
