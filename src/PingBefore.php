<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * Pings a URL before every run.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class PingBefore implements Modifier
{
    /**
     * @param  string  $url  The URL to ping.
     */
    public function __construct(public string $url)
    {
        Assert::url($this->url);
    }

    public function apply(Event $event): void
    {
        $event->pingBefore($this->url);
    }
}
