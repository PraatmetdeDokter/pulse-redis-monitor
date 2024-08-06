<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Cards;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
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
            'used_memory' => '#10b981',
            'max_memory' => '#9333ea',
        ];

        $this->colors = collect(config('pulse.recorders.'.RedisMonitorRecorder::class.'.colors', $defaultColors))->filter();
    }

    public function render(): Renderable
    {
        $memory = Pulse::graph(['used_memory', 'max_memory'], 'avg', $this->periodAsInterval());

        if (Request::hasHeader('X-Livewire')) {
            $this->dispatch('redis-monitor-chart-update', memory: $memory);
        }

        return View::make('redis-monitor::redis-monitor', [
            'memory' => $memory,
            'colors' => $this->colors
        ]);
    }
}
