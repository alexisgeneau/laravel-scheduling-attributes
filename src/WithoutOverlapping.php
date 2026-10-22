<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * Never two runs at once; the lock expires after the given number of minutes.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class WithoutOverlapping implements Modifier
{
    /**
     * @param  int  $expiresAt  Minutes before the lock expires (a day by default).
     */
    public function __construct(public int $expiresAt = 1440)
    {
        Assert::positive($this->expiresAt, 'lock expiry');
    }

    public function apply(Event $event): void
    {
        $event->withoutOverlapping($this->expiresAt);
    }
}
