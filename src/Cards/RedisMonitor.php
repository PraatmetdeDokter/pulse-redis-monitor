<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Cards;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;

#[Lazy]
class RedisMonitor extends Card
{
    public function render(): Renderable
    {
        $memory = Pulse::graph(['used_memory', 'max_memory'], 'avg', $this->periodAsInterval());

        if (Request::hasHeader('X-Livewire')) {
            $this->dispatch('redis-monitor-chart-update', memory: $memory);
        }

        return View::make('redis-monitor::redis-monitor', [
            'memory' => $memory
        ]);
    }
}
