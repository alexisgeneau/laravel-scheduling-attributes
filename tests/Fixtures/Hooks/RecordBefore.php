<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Hooks;

use Illuminate\Contracts\Config\Repository;
use SchedulingAttributes\Tests\Fixtures\Ran;

final class RecordBefore
{
    public function __construct(private readonly Repository $config) {}

    public function __invoke(): void
    {
        Ran::$hooks[] = 'before:'.$this->config->get('app.name');
    }
}
