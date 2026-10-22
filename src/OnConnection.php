<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use SchedulingAttributes\Contracts\JobOption;
use SchedulingAttributes\Support\Assert;

/**
 * The queue connection a scheduled job is pushed on.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class OnConnection implements JobOption
{
    /**
     * @param  string  $connection  The connection name.
     */
    public function __construct(public string $connection)
    {
        Assert::notBlank($this->connection, 'connection');
    }
}
