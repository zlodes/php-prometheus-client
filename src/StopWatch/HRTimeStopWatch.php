<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\StopWatch;

use Closure;

/**
 * @see https://www.php.net/manual/en/class.hrtime-stopwatch.php
 */
final class HRTimeStopWatch implements StopWatch
{
    private readonly float $startedAt;

    public function __construct(private readonly Closure $onStop)
    {
        $this->startedAt = hrtime(true);
    }

    public function stop(): void
    {
        $elapsed = (hrtime(true) - $this->startedAt) / 1e+9;

        ($this->onStop)($elapsed);
    }
}
