<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Components\Graphs;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Facades\Pulse;

class RemovedKeysGraphComponent extends RedisMonitorGraphComponent
{
    public function render(): Renderable
    {
        $this->items = Pulse::graph(['expired_keys', 'evicted_keys'], 'avg', $this->periodAsInterval());

        if (Request::hasHeader('X-Livewire')) {
            $this->dispatch('redis-monitor-removed-keys-chart-update', items: $this->items);
        }

        return View::make('redis-monitor::graphs.removed-keys');
    }
}