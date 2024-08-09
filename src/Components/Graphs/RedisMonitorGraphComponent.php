<?php

namespace PraatmetdeDokter\Pulse\RedisMonitor\Components\Graphs;

use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Livewire\Component;

class RedisMonitorGraphComponent extends Component
{
    /**
     * The collection of items gathered by pulse.
     */
    public Collection $items;

    /**
     * The collection of graph colors used for rendering graphs.
     *
     * Example structure:
     * [
     *     'primary'   => '#10b981',
     *     'secondary' => '#9333ea',
     * ]
     *
     * @var Collection<string, string>
     */
    public Collection $colors;

    /**
     * The usage period.
     *
     * @var '1_hour'|'6_hours'|'24_hours'|'7_days'|null
     */
    public ?string $period = '1_hour';


    public function periodAsInterval(): CarbonInterval
    {
        return CarbonInterval::hours(match ($this->period) {
            '6_hours' => 6,
            '24_hours' => 24,
            '7_days' => 168,
            default => 1,
        });
    }
}