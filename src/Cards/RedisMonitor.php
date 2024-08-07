<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Cards;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
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

    public function __construct()
    {
        $defaultColors = [
            'primary' => '#10b981',
            'secondary' => '#9333ea',
        ];

        $this->colors = collect(Config::get('pulse.recorders.'.RedisMonitorRecorder::class.'.colors', $defaultColors))->filter();
    }

    public function render(): Renderable
    {
        $memory = Pulse::graph(['used_memory', 'max_memory'], 'avg', $this->periodAsInterval());
        $active_keys = Pulse::graph(['keys_total', 'keys_with_expiration'], 'avg', $this->periodAsInterval());
        $removed_keys = Pulse::graph(['expired_keys', 'evicted_keys'], 'avg', $this->periodAsInterval());
        $avg_ttl = Pulse::graph(['avg_ttl'], 'avg', $this->periodAsInterval());
        $network = Pulse::graph(['redis_network_usage'], 'avg', $this->periodAsInterval());

        if (Request::hasHeader('X-Livewire')) {
            $this->dispatch('redis-monitor-memory-chart-update', memory: $memory);
            $this->dispatch('redis-monitor-active-keys-chart-update', keys: $active_keys);
            $this->dispatch('redis-monitor-removed-keys-chart-update', keys: $removed_keys);
            $this->dispatch('redis-monitor-ttl-chart-update', avg_ttl: $avg_ttl);
            $this->dispatch('redis-monitor-network-usage-chart-update', network: $network);
        }

        return View::make('redis-monitor::redis-monitor', [
            'memory' => $memory,
            'active_keys' => $active_keys,
            'removed_keys' => $removed_keys,
            'avg_ttl' => $avg_ttl,
            'network' => $network,
            'colors' => $this->colors
        ]);
    }
}
