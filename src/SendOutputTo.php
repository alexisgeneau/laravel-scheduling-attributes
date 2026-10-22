<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\CommandOnly;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Assert;

/**
 * Writes the output of the command to a file, replacing it.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class SendOutputTo implements CommandOnly, Modifier
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
        $event->sendOutputTo($this->path);
    }
}
