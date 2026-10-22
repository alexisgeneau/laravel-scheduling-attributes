<?php

declare(strict_types=1);

namespace SchedulingAttributes;

use Attribute;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Container\Container;
use SchedulingAttributes\Contracts\Modifier;
use SchedulingAttributes\Support\Invokable;

/**
 * Hands the event to an invokable class when it is registered, for anything the
 * scheduler can do that no other attribute covers: a macro added by a package, the
 * `*If` variants of the pings, `thenWithOutput()` with `$onlyIfOutputExists`...
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Tap implements Modifier
{
    /**
     * @param  class-string  $class  An invokable class, resolved by the container; its `__invoke()` receives the `Event`.
     */
    public function __construct(public string $class)
    {
        Invokable::assert($this->class);
    }

    public function apply(Event $event): void
    {
        $event->tap(Container::getInstance()->make($this->class));
    }
}
