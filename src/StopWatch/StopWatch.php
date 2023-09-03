<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\StopWatch;

use Closure;

/**
 * StopWatch timer starts immediately when it is created
 */
interface StopWatch
{
    /**
     * @param Closure(float): void $onStop
     */
    public function __construct(Closure $onStop);

    /**
     * Callback onStop must be called with elapsed time in seconds
     */
    public function stop(): void;
}
