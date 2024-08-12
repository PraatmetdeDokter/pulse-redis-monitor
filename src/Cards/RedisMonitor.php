<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Cards;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;
use PraatmetdeDokter\Pulse\RedisMonitor\Recorders\RedisMonitorRecorder;

#[Lazy]
class RedisMonitor extends Card
{
    /**
     * The graph colors.
     */
    public Collection $colors;

    /**
     * Collection containing boolean values storing whether a metric is enabled
     */
    public Collection $metrics;

    public function __construct()
    {
        $defaultColors = [
            'primary' => '#10b981',
            'secondary' => '#9333ea',
        ];

        $defaultMetrics = [
            'memory_usage' => true,
            'key_statistics' => true,
            'removed_keys' => true,
            'network_usage' => true
        ];

        $this->colors = collect($defaultColors)->merge(Config::get('pulse.recorders.'.RedisMonitorRecorder::class.'.colors', []));
        $this->metrics = collect($defaultMetrics)->merge(Config::get('pulse.recorders.'.RedisMonitorRecorder::class.'.metrics', []));
    }

    public function render(): Renderable
    {
        $memory = $this->metrics['memory_usage'] ? Pulse::graph(['used_memory', 'max_memory'], 'avg', $this->periodAsInterval()) : collect();
        $active_keys = $this->metrics['key_statistics'] ? Pulse::graph(['keys_total', 'keys_with_expiration'], 'avg', $this->periodAsInterval()) : collect();
        $removed_keys = $this->metrics['removed_keys'] ? Pulse::graph(['expired_keys', 'evicted_keys'], 'avg', $this->periodAsInterval()) : collect();
        $ttl = $this->metrics['key_statistics'] ? Pulse::graph(['avg_ttl'], 'avg', $this->periodAsInterval()) : collect();
        $network = $this->metrics['network_usage'] ? Pulse::graph(['redis_network_usage'], 'avg', $this->periodAsInterval()) : collect();

        if (Request::hasHeader('X-Livewire')) {
            if ($this->metrics['memory_usage']) {
                $this->dispatch('redis-monitor-memory-chart-update', items: $memory);
            }
            if ($this->metrics['key_statistics']) {
                $this->dispatch('redis-monitor-active-keys-chart-update', items: $active_keys);
                $this->dispatch('redis-monitor-ttl-chart-update', items: $ttl);
            }
            if ($this->metrics['removed_keys']) {
                $this->dispatch('redis-monitor-removed-keys-chart-update', items: $removed_keys);
            }
            if ($this->metrics['network_usage']) {
                $this->dispatch('redis-monitor-network-chart-update', items: $network);
            }
        }

        $empty = $memory->isEmpty() && $active_keys->isEmpty() && $removed_keys->isEmpty() && $ttl->isEmpty() && $network->isEmpty();

        return View::make('redis-monitor::redis-monitor', [
            'empty' => $empty,
            'memory' => $memory,
            'active_keys' => $active_keys,
            'removed_keys' => $removed_keys,
            'ttl' => $ttl,
            'network' => $network,
            'colors' => $this->colors,
            'metrics' => $this->metrics
        ]);
    }
}
