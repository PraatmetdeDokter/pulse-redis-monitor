<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Recorders;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Cache;
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

    /**
     * Array containing boolean values storing whether a feature is enabled
     */
    protected array $features;

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

        if ($this->features['memory_usage']) {
            $this->monitorMemoryUsage();
        }

        if ($this->features['key_statistics']) {
            $this->monitorKeyUsage();
        }

        if ($this->features['removed_keys']) {
            $this->monitorKeyStats();
        }

        if ($this->features['network_usage']) {
            $this->monitorNetworkUsage();
        }
    }

    /**
     * Monitors the memory usage of all configured Redis connections.
     */
    protected function monitorMemoryUsage(): void
    {
        foreach ($this->connections as $connection) {
            $output = Redis::connection($connection)->command('INFO', ['memory']);

            $this->recordMemoryUsage($connection, $output);
        }
    }

    /**
     * Monitors the key usage of all configured Redis connections.
     */
    protected function monitorKeyUsage(): void
    {
        foreach ($this->connections as $connection) {
            $output = Redis::connection($connection)->command('INFO', ['keyspace']);

            $this->recordKeyUsage($connection, $output);
        }
    }

    /**
     * Monitors the key stats of all configured Redis connections.
     */
    protected function monitorKeyStats(): void
    {
        foreach ($this->connections as $connection) {
            $output = Redis::connection($connection)->command('INFO', ['stats']);

            $this->recordKeyStats($connection, $output);
        }
    }

    /**
     * Monitors network usage of all configured Redis connections.
     */
    protected function monitorNetworkUsage(): void
    {
        foreach ($this->connections as $connection) {
            $output = Redis::connection($connection)->command('INFO', ['stats']);

            $this->recordNetworkUsage($connection, $output);
        }
    }

    /**
     * Records the memory usage data for a specific Redis connection.
     */
    protected function recordMemoryUsage(string $connection, array $output): void
    {
        if (! isset($output['used_memory'], $output['maxmemory'])) {
            return;
        }

        $this->pulse->record('used_memory', $connection, $output['used_memory'])->avg()->onlyBuckets();
        $this->pulse->record('max_memory', $connection, $output['maxmemory'])->avg()->onlyBuckets();
    }

    /**
     * Records the key usage data for all dbs for a specific connection.
     */
    protected function recordKeyUsage(string $connection, array $output): void
    {
        // Loop through each database in the output array
        foreach ($output as $dbKey => $statsString) {
            // Skip non-db keys or empty values
            if (strpos($dbKey, 'db') !== 0 || empty($statsString)) {
                continue;
            }

            $dbStats = explode(',', $statsString);

            $parsedStats = [];

            foreach ($dbStats as $stat) {
                [$key, $value] = explode('=', $stat);

                if ($key && $value !== null) {
                    $parsedStats[$key] = $value;
                }
            }

            $key = $connection.'_'.$dbKey;

            if (isset($parsedStats['keys']) && isset($parsedStats['expires'])) {
                $this->pulse->record('keys_total', $key, $parsedStats['keys'])->avg()->onlyBuckets();
                $this->pulse->record('keys_with_expiration', $key, $parsedStats['expires'])->avg()->onlyBuckets();
            }

            if (isset($parsedStats['avg_ttl'])) {
                $this->pulse->record('avg_ttl', $key, $parsedStats['avg_ttl'])->avg()->onlyBuckets();
            }
        }
    }

    /**
     * Records the expired and evicted key counts for a specific Redis connection.
     */
    protected function recordKeyStats(string $connection, array $output): void
    {
        if (! isset($output['expired_keys'], $output['evicted_keys'])) {
            return;
        }

        $prev_expired_keys = (int) Cache::get('total_expired_keys_'.$connection);
        $prev_evicted_keys = (int) Cache::get('total_evicted_keys_'.$connection);

        if (! is_null($prev_expired_keys) && ! is_null($prev_evicted_keys)) {
            $diff_expired = $output['expired_keys'] - $prev_expired_keys;
            $diff_evicted = $output['evicted_keys'] - $prev_evicted_keys;

            $this->pulse->record('expired_keys', $connection, $diff_expired)->avg()->onlyBuckets();
            $this->pulse->record('evicted_keys', $connection, $diff_evicted)->avg()->onlyBuckets();
        }

        Cache::put('total_expired_keys_'.$connection, $output['expired_keys']);
        Cache::put('total_evicted_keys_'.$connection, $output['evicted_keys']);
    }

    /**
     * Records the network usage since $this->interval for a specific Redis connection.
     */
    public function recordNetworkUsage(string $connection, array $output): void
    {
        if (! isset($output['total_net_input_bytes'], $output['total_net_output_bytes'])) {
            return;
        }

        $prev_expired_keys = (int) Cache::get('total_net_input_bytes_'.$connection, 0);
        $prev_evicted_keys = (int) Cache::get('total_net_output_bytes_'.$connection, 0);

        if ($prev_expired_keys !== 0 && $prev_evicted_keys !== 0) {
            $diff_output = $output['total_net_input_bytes'] - $prev_expired_keys;
            $diff_input = $output['total_net_output_bytes'] - $prev_evicted_keys;

            $diff = $diff_output + $diff_input;

            $this->pulse->record('redis_network_usage', $connection, $diff)->avg()->onlyBuckets();
        }

        Cache::put('total_net_input_bytes_'.$connection, $output['total_net_input_bytes']);
        Cache::put('total_net_output_bytes_'.$connection, $output['total_net_output_bytes']);
    }

    /**
     * Set the enabled features based on the configuration.
     */
    protected function setEnabledFeatures(): void
    {
        $this->features = [
            'memory_usage' => $this->config->get('redis_metrics.memory_usage', true),
            'key_statistics' => $this->config->get('redis_metrics.key_statistics', true), // Includes TTL
            'removed_keys' => $this->config->get('redis_metrics.removed_keys', true),
            'network_usage' => $this->config->get('redis_metrics.network_usage', true),
        ];
    }

    /**
     * Sets the interval, in minutes, for recording.
     */
    protected function setInterval(): void
    {
        $this->interval = $this->config->get('pulse.recorders.'.static::class.'.interval', 5);
    }

    /**
     * Sets the redis connection instance.
     */
    protected function setRedisConnection(): void
    {
        $this->connections = $this->config->get('pulse.recorders.'.static::class.'.connections', ['default']);
    }
}
