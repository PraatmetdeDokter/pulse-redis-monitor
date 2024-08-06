<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Recorders;

use Illuminate\Config\Repository;
use Laravel\Pulse\Events\SharedBeat;
use Laravel\Pulse\Pulse;

/**
 * @internal
 */
class RedisMonitorRecorder
{
    /**
     * The event to listen for
     */
    public string $listen = SharedBeat::class;

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

    public function record(SharedBeat $event): void
    {
        if ($event->time->minute % $this->getInterval() !== 0) {
            return;
        }
    }

    /**
     * Retrieves the interval, in minutes, for recording.
     *
     * @return int The interval in minutes.
     */
    protected function getInterval(): int
    {
        return $this->config->get('pulse.recorders.'.static::class.'.interval', 5);
    }
}
