<?php

declare(strict_types=1);

namespace SchedulingAttributes\Contracts;

/**
 * An attribute consumed when the job is pushed on the schedule rather than applied
 * to the event: the queue and the connection of `Schedule::job()`.
 */
interface JobOption {}
