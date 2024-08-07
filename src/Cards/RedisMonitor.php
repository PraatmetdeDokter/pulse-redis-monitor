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
        $keys = Pulse::graph(['keys_total', 'keys_with_expiration'], 'avg', $this->periodAsInterval());
        $avg_ttl = Pulse::graph(['avg_ttl'], 'avg', $this->periodAsInterval());

        if (Request::hasHeader('X-Livewire')) {
            $this->dispatch('redis-monitor-memory-chart-update', memory: $memory);
            $this->dispatch('redis-monitor-keys-chart-update', keys: $keys);
            $this->dispatch('redis-monitor-ttl-chart-update', avg_ttl: $avg_ttl);
        }

        return View::make('redis-monitor::redis-monitor', [
            'memory' => $memory,
            'keys' => $keys,
            'avg_ttl' => $avg_ttl,
            'colors' => $this->colors
        ]);
    }
}
