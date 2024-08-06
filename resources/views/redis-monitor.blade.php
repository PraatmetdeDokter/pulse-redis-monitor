<x-pulse::card id="redis-monitor-card" :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header
        name="Redis"
        details="past {{ $this->periodForHumans() }}"
    >
        <x-slot:icon>
            <x-pulse::icons.circle-stack />
        </x-slot:icon>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        @if ($memory->isEmpty())
            <x-pulse::no-results />
        @else
        <div class="grid gap-3 mx-px mb-px">
            @foreach ($memory as $connection => $data)
                <div wire:key="connections{{ $connection }}">
                    <h3 class="font-bold text-gray-700 dark:text-gray-300">
                        {{ $connection }}
                    </h3>
                    <div class="mt-3 relative">
                        <div
                            wire:ignore
                            class="h-14"
                            x-data="redisMonitorChart({
                                connection: '{{ $connection }}',
                                data: @js($data)
                            })"
                        >

                        <canvas x-ref="canvas" class="ring-1 ring-gray-900/5 dark:ring-gray-100/10 bg-gray-50 dark:bg-gray-800 rounded-md shadow-sm"></canvas>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </x-pulse::scroll>
</x-pulse::card>

@script
<script>
Alpine.data('redisMonitorChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.canvas,
            {
                type: 'line',
                data: {
                    labels: this.labels(config.data),
                    datasets: [
                        {
                            label: 'Used memory',
                            borderColor: '#ff0000',
                            data: config.data['used_memory'],
                            order: 0,
                        },
                        {
                            label: 'Max memory',
                            borderColor: '#000000',
                            data: config.data['max_memory'],
                            order: 1,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        autoPadding: false,
                        padding: {
                            top: 1,
                        },
                    },
                    datasets: {
                        line: {
                            borderWidth: 2,
                            borderCapStyle: 'round',
                            pointHitRadius: 10,
                            pointStyle: false,
                            tension: 0.2,
                            spanGaps: false,
                            segment: {
                                borderColor: (ctx) => ctx.p0.raw === 0 && ctx.p1.raw === 0 ? 'transparent' : undefined,
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false,
                        },
                        y: {
                            display: false,
                            min: 0,
                            max: this.highest(config.data),
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                },
            }
        )

        Livewire.on('redis-monitor-chart-update', ({ memory }) => {
            if (chart === undefined) {
                return
            }

            if (memory[config.connection] === undefined && chart) {
                chart.destroy()
                chart = undefined
                return
            }

            chart.data.labels = this.labels(memory[config.connection])
            chart.options.scales.y.max = this.highest(memory[config.connection])
            chart.data.datasets[0].data = memory[config.connection]['used_memory']
            chart.data.datasets[1].data = memory[config.connection]['max_memory']
            chart.update()
        })
    },
    labels(data) {
        return Object.keys(data['used_memory'])
    },
    highest(readings) {
        return Math.max(...Object.values(readings).map(dataset => Math.max(...Object.values(dataset))))
    }
}))
</script>
@endscript