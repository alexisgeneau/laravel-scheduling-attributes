<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * Only in the given application environments.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Environments implements Modifier
{
    /** @var list<string> */
    public array $environments;

    /**
     * @param  string  ...$environments  Environment names, as in APP_ENV.
     */
    public function __construct(string ...$environments)
    {
        $this->environments = Assert::nonEmptyList(array_values($environments), 'environment');
    }

    public function apply(Event $event): void
    {
        $event->environments($this->environments);
    }
}
