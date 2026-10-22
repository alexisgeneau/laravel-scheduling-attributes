<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\CommandOnly;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * Emails the output of the command when it fails.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class EmailOutputOnFailure implements CommandOnly, Modifier
{
    /** @var list<string> */
    public array $addresses;

    /**
     * @param  string  ...$addresses  Email addresses.
     */
    public function __construct(string ...$addresses)
    {
        $this->addresses = Assert::emails(array_values($addresses));
    }

    public function apply(Event $event): void
    {
        $event->emailOutputOnFailure($this->addresses);
    }
}
