<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Components\Graphs;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Facades\Pulse;

class MemoryGraphComponent extends RedisMonitorGraphComponent
{
    public function render(): Renderable
    {
        $this->items = Pulse::graph(['used_memory', 'max_memory'], 'avg', $this->periodAsInterval());

        if (Request::hasHeader('X-Livewire')) {
            $this->dispatch('redis-monitor-memory-chart-update', items: $this->items);
        }

        return View::make('redis-monitor::graphs.memory');
    }
}