<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Hooks;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Contracts\Config\Repository;

/** A #[Tap] target: receives the event when it is registered, with its dependencies injected. */
final class NameAfterApplication
{
    public function __construct(private readonly Repository $config) {}

    public function __invoke(Event $event): void
    {
        $event->name('tapped by '.$this->config->get('app.name'));
    }
}
