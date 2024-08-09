<div class="grid gap-3 mx-px mb-px" wire:poll.60s>
    <h3 class="font-bold text-gray-700 dark:text-gray-300">TTL</h3>
    @foreach ($items as $connection => $data)
        <div wire:key="keys-connection-{{ $connection }}">
            <h3 class="font-bold text-gray-700 dark:text-gray-300">
                {{ $connection }}
            </h3>
            <div class="mt-3 relative">
                <div
                    wire:ignore
                    class="h-14"
                    x-data="redisMonitorTtlChart({
                        connection: '{{ $connection }}',
                        colors: @js($colors),
                        data: @js($data)
                    })"
                >

                <canvas x-ref="TtlCanvas" class="ring-1 ring-gray-900/5 dark:ring-gray-100/10 bg-gray-50 dark:bg-gray-800 rounded-md shadow-sm"></canvas>
            </div>
        </div>
    @endforeach
</div>

@script
<script>
Alpine.data('redisMonitorTtlChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.TtlCanvas,
            {
                type: 'line',
                data: {
                    labels: this.labels(config.data),
                    datasets: [
                        {
                            label: 'Average ttl',
                            borderColor: config.colors['secondary'],
                            data: config.data['avg_ttl'],
                            order: 0,
                        }
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

        Livewire.on('redis-monitor-ttl-chart-update', ({ items }) => {
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
            chart.data.datasets[0].data = items[config.connection]['avg_ttl']
            chart.update()
        })
    },
    labels(data) {
        return Object.keys(data['avg_ttl'])
    },
    highest(readings) {
        return Math.max(...Object.values(readings).map(dataset => Math.max(...Object.values(dataset))))
    },
    labelValue(millisecondsString) {
        milliseconds = parseFloat(millisecondsString.replace(/\./g, '').replace(',', '.'))
        if (milliseconds === 0) {
            return '0 milliseconds'
        }

        const millisecondsInSecond = 1000
        const millisecondsInMinute = millisecondsInSecond * 60
        const millisecondsInHour = millisecondsInMinute * 60
        const millisecondsInDay = millisecondsInHour * 24

        const days = Math.floor(milliseconds / millisecondsInDay)
        milliseconds %= millisecondsInDay
        const hours = Math.floor(milliseconds / millisecondsInHour)
        milliseconds %= millisecondsInHour
        const minutes = Math.floor(milliseconds / millisecondsInMinute)
        milliseconds %= millisecondsInMinute
        const seconds = Math.floor(milliseconds / millisecondsInSecond)

        let label = ''

        if (days > 0) {
            label = `${days} day${days > 1 ? 's' : ''}, ${minutes} minute${minutes > 1 ? 's' : ''}`
        } else if (hours > 0) {
            label = `${hours} hour${hours > 1 ? 's' : ''}, ${minutes} minute${minutes > 1 ? 's' : ''}`
        } else if (minutes > 0) {
            label = `${minutes} minute${minutes > 1 ? 's' : ''}, ${seconds} second${seconds > 1 ? 's' : ''}`
        } else if (seconds > 0) {
            label = `${seconds} second${seconds > 1 ? 's' : ''}, ${milliseconds} millisecond${milliseconds !== 1 ? 's' : ''}`
        } else {
            label = `${milliseconds} millisecond${milliseconds !== 1 ? 's' : ''}`
        }

        return label.trim()
    }
}))
</script>
@endscript