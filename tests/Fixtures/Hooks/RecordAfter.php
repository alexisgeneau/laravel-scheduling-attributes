<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Hooks;

use Illuminate\Support\Stringable;
use SchedulingAttributes\Tests\Fixtures\Ran;

final class RecordAfter
{
    public function __invoke(Stringable $output): void
    {
        Ran::$hooks[] = 'after:'.$output->toString();
    }
}
