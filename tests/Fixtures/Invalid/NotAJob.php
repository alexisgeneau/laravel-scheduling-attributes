<?php

declare(strict_types=1);

namespace SchedulingAttributes\Tests\Fixtures\Invalid;

use SchedulingAttributes\Daily;

#[Daily]
final class NotAJob {}
