<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * The timezone the times of the frequency are expressed in.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Timezone implements Modifier
{
    /**
     * @param  string  $timezone  A timezone identifier such as "Europe/Paris".
     */
    public function __construct(public string $timezone)
    {
        Assert::timezone($this->timezone);
    }

    public function apply(Event $event): void
    {
        $event->timezone($this->timezone);
    }
}
