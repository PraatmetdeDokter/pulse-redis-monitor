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
     * Array of redis connection names to record
     */
    protected array $connections;


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

        foreach ($this->connections as $connection) {
            $output = Redis::connection($connection)->command('INFO');

            $this->recordMemoryUsage($connection, $output);
            $this->recordKeyUsage($connection, $output);
        }
    }

    protected function recordMemoryUsage(string $connection, array $output): void
    {
        $this->pulse->record('used_memory', $connection, $output['used_memory'])->avg()->onlyBuckets();
        $this->pulse->record('max_memory', $connection, $output['maxmemory'])->avg()->onlyBuckets();
    }

    protected function recordKeyUsage(string $connection, array $output): void
    {
        $db0Stats = explode(',', $output['db0']);

        $parsedStats = [];

        foreach ($db0Stats as $stat) {
            [$key, $value] = explode('=', $stat);
            if ($key && $value !== NULL) {
                $parsedStats[$key] = $value;
            }
        }

        if (isset($parsedStats['keys'])) {
            $this->pulse->record('keys_total', $connection, $parsedStats['keys'])->avg()->onlyBuckets();
        }

        if (isset($parsedStats['expires'])) {
            $this->pulse->record('keys_with_expiration', $connection, $parsedStats['expires'])->avg()->onlyBuckets();
        }

        if (isset($parsedStats['avg_ttl'])) {
            $this->pulse->record('avg_ttl', $connection, $parsedStats['avg_ttl'])->avg()->onlyBuckets();
        }
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
        $this->connections = $this->config->get('pulse.recorders.'.static::class.'.connections', ['default']);
    }
}
