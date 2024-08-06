<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Cards;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;

/**
 * @internal
 */
#[Lazy]
class RedisMonitor extends Card
{
    public function render(): Renderable
    {
        return View::make('redis-monitor::redis-monitor');
    }
}
