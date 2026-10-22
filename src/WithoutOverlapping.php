<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use ReflectionMethod;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;
use SchedulingAttributes\Support\UnsupportedMethod;

/**
 * Never two runs at once; the lock expires after the given number of minutes.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class WithoutOverlapping implements Modifier
{
    /**
     * @param  int  $expiresAt  Minutes before the lock expires (a day by default).
     * @param  bool  $releaseOnTerminationSignals  Whether a SIGINT or SIGTERM releases the lock (Laravel 13).
     */
    public function __construct(public int $expiresAt = 1440, public bool $releaseOnTerminationSignals = true)
    {
        Assert::positive($this->expiresAt, 'lock expiry');
    }

    public function apply(Event $event): void
    {
        if ($this->releaseOnTerminationSignals) {
            $event->withoutOverlapping($this->expiresAt);

            return;
        }

        if ((new ReflectionMethod($event, 'withoutOverlapping'))->getNumberOfParameters() < 2) {
            throw new UnsupportedMethod('withoutOverlapping($expiresAt, $releaseOnTerminationSignals)');
        }

        $event->withoutOverlapping($this->expiresAt, false);
    }
}
