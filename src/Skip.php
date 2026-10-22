<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Invokable;

/**
 * Skipped when the invokable class returns true.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Skip implements Modifier
{
    /**
     * @param  class-string  $class  An invokable class; its __invoke() receives what the container can resolve and returns the verdict.
     */
    public function __construct(public string $class)
    {
        Invokable::assert($this->class);
    }

    public function apply(Event $event): void
    {
        $event->skip(Invokable::condition($this->class));
    }
}
