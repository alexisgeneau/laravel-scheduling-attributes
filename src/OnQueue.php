<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use SchedulingAttributes\Contracts\JobOption;
use SchedulingAttributes\Support\Assert;

/**
 * The queue a scheduled job is pushed on.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class OnQueue implements JobOption
{
    /**
     * @param  string  $queue  The queue name.
     */
    public function __construct(public string $queue)
    {
        Assert::notBlank($this->queue, 'queue');
    }
}
