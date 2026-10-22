<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\CommandOnly;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * The system user the command runs as.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class User implements CommandOnly, Modifier
{
    /**
     * @param  string  $user  A system user name.
     */
    public function __construct(public string $user)
    {
        Assert::notBlank($this->user, 'user');
    }

    public function apply(Event $event): void
    {
        $event->user($this->user);
    }
}
