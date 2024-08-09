<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Livewire\LivewireManager;
use PraatmetdeDokter\Pulse\RedisMonitor\Cards\RedisMonitor;
use PraatmetdeDokter\Pulse\RedisMonitor\Components\Graphs\ActiveKeysGraphComponent;
use PraatmetdeDokter\Pulse\RedisMonitor\Components\Graphs\MemoryGraphComponent;
use PraatmetdeDokter\Pulse\RedisMonitor\Components\Graphs\NetworkUsageGraphComponent;
use PraatmetdeDokter\Pulse\RedisMonitor\Components\Graphs\RemovedKeysGraphComponent;
use PraatmetdeDokter\Pulse\RedisMonitor\Components\Graphs\TtlGraphComponent;

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
            $livewire->component('graphs.memory', MemoryGraphComponent::class);
            $livewire->component('graphs.active-keys', ActiveKeysGraphComponent::class);
            $livewire->component('graphs.removed-keys', RemovedKeysGraphComponent::class);
            $livewire->component('graphs.ttl', TtlGraphComponent::class);
            $livewire->component('graphs.network-usage', NetworkUsageGraphComponent::class);

            $livewire->component('pulse.redis-monitor', RedisMonitor::class);
        });
    }
}
