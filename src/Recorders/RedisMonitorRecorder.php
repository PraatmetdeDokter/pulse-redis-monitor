<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Recorders;

use Illuminate\Config\Repository;
use Laravel\Pulse\Pulse;

/**
 * @internal
 */
class RedisMonitorRecorder
{
    /**
     * Recorder instance
     */
    protected Pulse $pulse;

    /**
     * Pulse recorder config
     */
    protected Repository $config;

    public function __construct(Pulse $pulse, Repository $config)
    {
        $this->pulse = $pulse;
        $this->config = $config;
    }
}
