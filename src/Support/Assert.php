<?php

declare(strict_types=1);

namespace SchedulingAttributes\Support;

use Cron\CronExpression;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use SchedulingAttributes\Day;

/**
 * Validation of attribute arguments, at construction, so that a wrong value is
 * reported with the attribute and the class instead of surfacing as a cron error.
 */
final class Assert
{
    /** A time of day as the scheduler accepts it: "H:i" or "H". */
    public static function time(string $time): string
    {
        if (preg_match('/^(\d{1,2})(?::(\d{1,2}))?$/', $time, $parts) !== 1) {
            throw new InvalidArgumentException(sprintf('"%s" is not a time of day; use "HH:MM" such as "03:30".', $time));
        }

        self::hour((int) $parts[1]);
        self::minute((int) ($parts[2] ?? 0));

        return $time;
    }

    public static function hour(int $hour): int
    {
        return self::between($hour, 0, 23, 'hour');
    }

    public static function minute(int $minute): int
    {
        return self::between($minute, 0, 59, 'minute');
    }

    /**
     * Minutes past the hour: one or several, as hourlyAt() and everyTwoHours() accept.
     *
     * @param  int|list<int>  $offset
     * @return int|list<int>
     */
    public static function minutes(int|array $offset): int|array
    {
        if (is_int($offset)) {
            return self::minute($offset);
        }

        if ($offset === []) {
            throw new InvalidArgumentException('give at least one minute past the hour.');
        }

        return array_map(static fn (int $minute): int => self::minute($minute), $offset);
    }

    public static function dayOfMonth(int $day): int
    {
        return self::between($day, 1, 31, 'day of the month');
    }

    public static function month(int $month): int
    {
        return self::between($month, 1, 12, 'month');
    }

    public static function dayOfWeek(Day|int $day): int
    {
        return $day instanceof Day ? $day->value : self::between($day, 0, 6, 'day of the week (0 is Sunday)');
    }

    /**
     * @param  Day|int|list<Day|int>  $days
     * @return int|list<int>
     */
    public static function daysOfWeek(Day|int|array $days): int|array
    {
        if (! is_array($days)) {
            return self::dayOfWeek($days);
        }

        if ($days === []) {
            throw new InvalidArgumentException('give at least one day of the week.');
        }

        return array_map(static fn (Day|int $day): int => self::dayOfWeek($day), $days);
    }

    public static function cron(string $expression): string
    {
        if (! CronExpression::isValidExpression($expression)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid cron expression.', $expression));
        }

        return $expression;
    }

    public static function timezone(string $timezone): string
    {
        try {
            new DateTimeZone($timezone);
        } catch (Exception) {
            throw new InvalidArgumentException(sprintf('"%s" is not a known timezone.', $timezone));
        }

        return $timezone;
    }

    public static function url(string $url): string
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(sprintf('"%s" is not a URL.', $url));
        }

        return $url;
    }

    /**
     * @param  list<string>  $addresses
     * @return list<string>
     */
    public static function emails(array $addresses): array
    {
        if ($addresses === []) {
            throw new InvalidArgumentException('give at least one email address.');
        }

        foreach ($addresses as $address) {
            if (filter_var($address, FILTER_VALIDATE_EMAIL) === false) {
                throw new InvalidArgumentException(sprintf('"%s" is not an email address.', $address));
            }
        }

        return $addresses;
    }

    public static function notBlank(string $value, string $what): string
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException(sprintf('the %s cannot be blank.', $what));
        }

        return $value;
    }

    public static function positive(int $value, string $what): int
    {
        if ($value < 1) {
            throw new InvalidArgumentException(sprintf('the %s must be at least 1, %d given.', $what, $value));
        }

        return $value;
    }

    /**
     * @template T
     *
     * @param  list<T>  $values
     * @return list<T>
     */
    public static function nonEmptyList(array $values, string $what): array
    {
        if ($values === []) {
            throw new InvalidArgumentException(sprintf('give at least one %s.', $what));
        }

        return $values;
    }

    private static function between(int $value, int $min, int $max, string $what): int
    {
        if ($value < $min || $value > $max) {
            throw new InvalidArgumentException(sprintf('%d is not a valid %s; expected %d to %d.', $value, $what, $min, $max));
        }

        return $value;
    }
}
