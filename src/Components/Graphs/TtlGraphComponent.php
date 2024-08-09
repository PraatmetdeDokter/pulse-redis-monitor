<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Components\Graphs;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Facades\Pulse;

class TtlGraphComponent extends RedisMonitorGraphComponent
{
    public function render(): Renderable
    {
        $this->items = Pulse::graph(['avg_ttl'], 'avg', $this->periodAsInterval());

        if (Request::hasHeader('X-Livewire')) {
            $this->dispatch('redis-monitor-ttl-chart-update', items: $this->items);
        }

        return View::make('redis-monitor::graphs.ttl');
    }
}