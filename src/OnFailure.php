<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Invokable;

/**
 * Invokes a class after every failed run; a `Stringable $output` parameter receives the output of the command.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class OnFailure implements Modifier
{
    /**
     * @param  class-string  $class  An invokable class, resolved by the container when the hook fires.
     */
    public function __construct(public string $class)
    {
        Invokable::assert($this->class);
    }

    public function apply(Event $event): void
    {
        $event->onFailure(Invokable::callback($this->class));
    }
}
