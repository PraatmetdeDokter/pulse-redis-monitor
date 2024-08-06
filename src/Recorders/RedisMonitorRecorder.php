<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Recorders;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Redis;
use Laravel\Pulse\Events\SharedBeat;
use Laravel\Pulse\Pulse;

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

    /**
     * Interval of recorder in minutes
     */
    protected int $interval;

    /**
     * Redis connection name
     */
    protected string $redis;

    public function __construct(Pulse $pulse, Repository $config)
    {
        $this->pulse = $pulse;
        $this->config = $config;

        $this->setInterval();
        $this->setRedisConnection();
    }

    public function record(SharedBeat $event): void
    {
        if ($event->time->minute % $this->interval !== 0 && $event->time->second === 0) {
            return;
        }

        $this->recordMemoryUsage();
    }

    protected function recordMemoryUsage(): void
    {
        $output = Redis::connection($this->connection)->command('INFO');

        $this->pulse->record('used_memory', $this->connection, $output['used_memory'])->avg()->onlyBuckets();
        $this->pulse->record('max_memory', $this->connection, $output['maxmemory'])->avg()->onlyBuckets();
    }

    /**
     * Sets the interval, in minutes, for recording.
     */
    protected function setInterval(): void
    {
        $this->interval = $this->config->get('pulse.recorders.'.static::class.'.interval', 5);
    }

    /**
     * Sets the redis connection instance
     */
    protected function setRedisConnection(): void
    {
        $this->connection = $this->config->get('pulse.recorders.'.static::class.'.connection', 'default');
    }
}
