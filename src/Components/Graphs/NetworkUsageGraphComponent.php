<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Components\Graphs;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Facades\Pulse;

class NetworkUsageGraphComponent extends RedisMonitorGraphComponent
{
    public function render(): Renderable
    {
        $this->items = Pulse::graph(['redis_network_usage'], 'avg', $this->periodAsInterval());

        if (Request::hasHeader('X-Livewire')) {
            $this->dispatch('redis-monitor-network-usage-chart-update', items: $this->items);
        }

        return View::make('redis-monitor::graphs.network-usage');
    }
}