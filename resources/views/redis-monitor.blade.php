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
        @if ($memory->isEmpty() && $keys->isEmpty() && $avg_ttl->isEmpty())
            <x-pulse::no-results />
        @else
        <div class="grid gap-3 mx-px mb-px">
            <h3 class="font-bold text-gray-700 dark:text-gray-300">Memory</h3>
            @foreach ($memory as $connection => $data)
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

        <div class="grid gap-3 mx-px mb-px">
            <h3 class="font-bold text-gray-700 dark:text-gray-300">Keys</h3>
            @foreach ($keys as $connection => $data)
                <div wire:key="keys-connection-{{ $connection }}">
                    <h3 class="font-bold text-gray-700 dark:text-gray-300">
                        {{ $connection }}
                    </h3>
                    <div class="mt-3 relative">
                        <div
                            wire:ignore
                            class="h-14"
                            x-data="redisMonitorKeysChart({
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

        <div class="grid gap-3 mx-px mb-px">
            <h3 class="font-bold text-gray-700 dark:text-gray-300">TTL</h3>
            @foreach ($avg_ttl as $connection => $data)
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

        Livewire.on('redis-monitor-memory-chart-update', ({ memory }) => {
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

Alpine.data('redisMonitorKeysChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.canvas,
            {
                type: 'line',
                data: {
                    labels: this.labels(config.data),
                    datasets: [
                        {
                            label: 'Keys with exipration',
                            borderColor: config.colors['secondary'],
                            data: config.data['keys_with_expiration'],
                            order: 0,
                        },
                        {
                            label: 'Total keys',
                            borderColor: config.colors['primary'],
                            data: config.data['keys_total'],
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
                                    .map(item => `${item.dataset.label}: ${item.formattedValue}`)
                                    .join(', '),
                                label: () => null,
                            },
                        },
                    },
                },
            }
        )

        Livewire.on('redis-monitor-keys-chart-update', ({ keys }) => {
            if (chart === undefined) {
                return
            }

            if (keys[config.connection] === undefined && chart) {
                chart.destroy()
                chart = undefined
                return
            }

            chart.data.labels = this.labels(keys[config.connection])
            chart.options.scales.y.max = this.highest(keys[config.connection])
            chart.data.datasets[0].data = keys[config.connection]['keys_with_expiration']
            chart.data.datasets[1].data = keys[config.connection]['keys_total']
            chart.update()
        })
    },
    labels(data) {
        return Object.keys(data['keys_total'])
    },
    highest(readings) {
        return Math.max(...Object.values(readings).map(dataset => Math.max(...Object.values(dataset))))
    }
}))

Alpine.data('redisMonitorTtlChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.canvas,
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

        Livewire.on('redis-monitor-ttl-chart-update', ({ avg_ttl }) => {
            if (chart === undefined) {
                return
            }

            if (avg_ttl[config.connection] === undefined && chart) {
                chart.destroy()
                chart = undefined
                return
            }

            chart.data.labels = this.labels(avg_ttl[config.connection])
            chart.options.scales.y.max = this.highest(avg_ttl[config.connection])
            chart.data.datasets[0].data = avg_ttl[config.connection]['avg_ttl']
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