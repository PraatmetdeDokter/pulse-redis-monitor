<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Cards;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
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
        $data = Pulse::graph([
            'used_memory',
            'max_memory',
            'keys_total',
            'keys_with_expiration',
            'expired_keys',
            'evicted_keys',
            'avg_ttl',
            'redis_network_usage',
        ], 'avg', $this->periodAsInterval());

        return View::make('redis-monitor::redis-monitor', [
            'empty' => $data->isEmpty(),
            'period' => $this->period,
            'colors' => $this->colors,
        ]);
    }
}
