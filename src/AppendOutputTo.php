<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\CommandOnly;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * Appends the output of the command to a file.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class AppendOutputTo implements CommandOnly, Modifier
{
    /**
     * @param  string  $path  The path of the file.
     */
    public function __construct(public string $path)
    {
        Assert::notBlank($this->path, 'path');
    }

    public function apply(Event $event): void
    {
        $event->appendOutputTo($this->path);
    }
}
