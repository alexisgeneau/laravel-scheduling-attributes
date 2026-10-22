<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * The name of the events, shown by schedule:list and used for the locks.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Name implements Modifier
{
    /**
     * @param  string  $name  A unique name.
     */
    public function __construct(public string $name)
    {
        Assert::notBlank($this->name, 'name');
    }

    public function apply(Event $event): void
    {
        $event->name($this->name);
    }
}
