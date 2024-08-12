<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Livewire\LivewireManager;
use PraatmetdeDokter\Pulse\RedisMonitor\Cards\RedisMonitor;

/**
 * @internal
 */
class RedisMonitorServiceProvider extends BaseServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'redis-monitor');

        $this->callAfterResolving('livewire', function (LivewireManager $livewire) {
            $livewire->component('pulse.redis-monitor', RedisMonitor::class);
        });
    }
}
