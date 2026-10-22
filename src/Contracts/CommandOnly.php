<?php

declare(strict_types=1);

namespace SchedulingAttributes\Contracts;

/**
 * Marks a modifier the scheduler only supports on commands, never on jobs: output
 * capture, running in the background, running as another user.
 */
interface CommandOnly {}
