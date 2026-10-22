<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Hooks;

use SchedulingAttributes\Tests\Fixtures\Ran;

final class RecordSuccess
{
    public function __invoke(): void
    {
        Ran::$hooks[] = 'success';
    }
}
