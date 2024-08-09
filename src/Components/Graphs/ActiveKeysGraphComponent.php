<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Components\Graphs;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Facades\Pulse;

class ActiveKeysGraphComponent extends RedisMonitorGraphComponent
{
    public function render(): Renderable
    {
        $this->items = Pulse::graph(['keys_total', 'keys_with_expiration'], 'avg', $this->periodAsInterval());

        if (Request::hasHeader('X-Livewire')) {
            $this->dispatch('redis-monitor-active-keys-chart-update', items: $this->items);
        }

        return View::make('redis-monitor::graphs.active-keys');
    }
}