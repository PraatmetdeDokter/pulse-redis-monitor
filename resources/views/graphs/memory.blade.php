<div class="grid gap-3 mx-px mb-px" wire:poll.60s>
    <h3 class="font-bold text-gray-700 dark:text-gray-300">Memory</h3>
    @foreach ($items as $connection => $data)
        <div wire:key="memory-connection-{{ $connection }}">
            <h3 class="font-bold text-gray-700 dark:text-gray-300">
                {{ $connection }}
            </h3>
            <div class="mt-3 relative">
                <div
                    wire:ignore
                    class="h-14"
                    x-data="redisMonitorMemoryChart({
                        connection: '{{ $connection }}',
                        colors: @js($colors),
                        data: @js($data)
                    })"
                >

                <canvas x-ref="canvas" class="ring-1 ring-gray-900/5 dark:ring-gray-100/10 bg-gray-50 dark:bg-gray-800 rounded-md shadow-sm"></canvas>
            </div>
        </div>
    @endforeach
</div>

@script
<script>
Alpine.data('redisMonitorMemoryChart', (config) => ({
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
                            borderColor: config.colors['secondary'],
                            data: config.data['used_memory'],
                            order: 0,
                        },
                        {
                            label: 'Max memory',
                            borderColor: config.colors['primary'],
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
                        tooltip: {
                            mode: 'index',
                            position: 'nearest',
                            intersect: false,
                            callbacks: {
                                beforeBody: (context) => context
                                    .map(item => `${item.dataset.label}: ${this.labelValue(item.formattedValue)}`)
                                    .join(', '),
                                label: () => null,
                            },
                        },
                    },
                },
            }
        )

        Livewire.on('redis-monitor-memory-chart-update', ({ items }) => {
            if (chart === undefined) {
                return
            }

            if (items[config.connection] === undefined && chart) {
                chart.destroy()
                chart = undefined
                return
            }

            chart.data.labels = this.labels(items[config.connection])
            chart.options.scales.y.max = this.highest(items[config.connection])
            chart.data.datasets[0].data = items[config.connection]['used_memory']
            chart.data.datasets[1].data = items[config.connection]['max_memory']
            chart.update()
        })
    },
    labels(data) {
        return Object.keys(data['used_memory'])
    },
    highest(readings) {
        return Math.max(...Object.values(readings).map(dataset => Math.max(...Object.values(dataset))))
    },
    labelValue(bytesString) {
        bytes = parseFloat(bytesString.replace(/\./g, '').replace(',', '.'))
        if (bytes === 0) {
            return '0 Bytes'
        }

        const k = 1024
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
        const i = Math.floor(Math.log(bytes) / Math.log(k))
        const value = parseFloat((bytes / Math.pow(k, i)).toFixed(2))

        return `${value} ${sizes[i]}`
    }
}))
</script>
@endscript