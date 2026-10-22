<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

// Scheduled in code, next to the attributes, as an application would.
Schedule::command('inspire')->hourly();
