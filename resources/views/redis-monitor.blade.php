<x-pulse::card id="redis-monitor-card" :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header
        name="Redis"
        details="past {{ $this->periodForHumans() }}"
    >
        <x-slot:icon>
            <x-pulse::icons.circle-stack />
        </x-slot:icon>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand">
        @if ($empty)
            <x-pulse::no-results />
        @else

        {{-- Start of memory graph --}}
        @if ($metrics['memory_usage'])
        <div class="grid gap-3 mx-px mb-px" wire:poll.60s>
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

                        <canvas x-ref="memoryCanvas" class="ring-1 ring-gray-900/5 dark:ring-gray-100/10 bg-gray-50 dark:bg-gray-800 rounded-md shadow-sm"></canvas>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Start of active keys graph --}}
        @if ($metrics['key_statistics'])
        <div class="grid gap-3 mx-px mb-px" wire:poll.60s>
            <h3 class="font-bold text-gray-700 dark:text-gray-300">Active keys</h3>
            @foreach ($active_keys as $connection => $data)
                <div wire:key="keys-connection-{{ $connection }}">
                    <h3 class="font-bold text-gray-700 dark:text-gray-300">
                        {{ $connection }}
                    </h3>
                    <div class="mt-3 relative">
                        <div
                            wire:ignore
                            class="h-14"
                            x-data="redisMonitorActiveKeysChart({
                                connection: '{{ $connection }}',
                                colors: @js($colors),
                                data: @js($data)
                            })"
                        >

                        <canvas x-ref="activeKeysCanvas" class="ring-1 ring-gray-900/5 dark:ring-gray-100/10 bg-gray-50 dark:bg-gray-800 rounded-md shadow-sm"></canvas>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Start of removed keys graph --}}
        @if ($metrics['removed_keys'])
        <div class="grid gap-3 mx-px mb-px" wire:poll.60s>
            <h3 class="font-bold text-gray-700 dark:text-gray-300">Removed keys</h3>
            @foreach ($removed_keys as $connection => $data)
                <div wire:key="keys-connection-{{ $connection }}">
                    <h3 class="font-bold text-gray-700 dark:text-gray-300">
                        {{ $connection }}
                    </h3>
                    <div class="mt-3 relative">
                        <div
                            wire:ignore
                            class="h-14"
                            x-data="redisMonitorRemovedKeysChart({
                                connection: '{{ $connection }}',
                                colors: @js($colors),
                                data: @js($data)
                            })"
                        >

                        <canvas x-ref="removedKeysCanvas" class="ring-1 ring-gray-900/5 dark:ring-gray-100/10 bg-gray-50 dark:bg-gray-800 rounded-md shadow-sm"></canvas>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Start of TTL graph --}}
        @if ($metrics['key_statistics'])
        <div class="grid gap-3 mx-px mb-px" wire:poll.60s>
            <h3 class="font-bold text-gray-700 dark:text-gray-300">TTL</h3>
            @foreach ($ttl as $connection => $data)
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
        @endif

        {{-- Start of network graph --}}
        @if ($metrics['network_usage'])
        <div class="grid gap-3 mx-px mb-px" wire:poll.60s>
            <h3 class="font-bold text-gray-700 dark:text-gray-300">Network usage</h3>
            @foreach ($network as $connection => $data)
                <div wire:key="keys-connection-{{ $connection }}">
                    <h3 class="font-bold text-gray-700 dark:text-gray-300">
                        {{ $connection }}
                    </h3>
                    <div class="mt-3 relative">
                        <div
                            wire:ignore
                            class="h-14"
                            x-data="redisMonitorNetworkUsageChart({
                                connection: '{{ $connection }}',
                                colors: @js($colors),
                                data: @js($data)
                            })"
                        >

                        <canvas x-ref="networkUsageCanvas" class="ring-1 ring-gray-900/5 dark:ring-gray-100/10 bg-gray-50 dark:bg-gray-800 rounded-md shadow-sm"></canvas>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
        @endif
    </x-pulse::scroll>
</x-pulse::card>

@script
<script>
Alpine.data('redisMonitorMemoryChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.memoryCanvas,
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

Alpine.data('redisMonitorActiveKeysChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.activeKeysCanvas,
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
                                    .map(item => `${item.dataset.label}: ${item.formattedValue}`)
                                    .join(', '),
                                label: () => null,
                            },
                        },
                    },
                },
            }
        )

        Livewire.on('redis-monitor-active-keys-chart-update', ({ items }) => {
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
            chart.data.datasets[0].data = items[config.connection]['keys_with_expiration']
            chart.data.datasets[1].data = items[config.connection]['keys_total']
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

Alpine.data('redisMonitorRemovedKeysChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.removedKeysCanvas,
            {
                type: 'line',
                data: {
                    labels: this.labels(config.data),
                    datasets: [
                        {
                            label: 'Expired keys',
                            borderColor: config.colors['secondary'],
                            data: config.data['expired_keys'],
                            order: 0,
                        },
                        {
                            label: 'Evicted keys',
                            borderColor: config.colors['primary'],
                            data: config.data['evicted_keys'],
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

        Livewire.on('redis-monitor-removed-keys-chart-update', ({ items }) => {
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
            chart.data.datasets[0].data = items[config.connection]['expired_keys']
            chart.data.datasets[1].data = items[config.connection]['evicted_keys']
            chart.update()
        })
    },
    labels(data) {
        return Object.keys(data['expired_keys'])
    },
    highest(readings) {
        return Math.max(...Object.values(readings).map(dataset => Math.max(...Object.values(dataset))))
    }
}))

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

Alpine.data('redisMonitorNetworkUsageChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.networkUsageCanvas,
            {
                type: 'line',
                data: {
                    labels: this.labels(config.data),
                    datasets: [
                        {
                            label: 'Network usage',
                            borderColor: config.colors['secondary'],
                            data: config.data['redis_network_usage'],
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

        Livewire.on('redis-monitor-network-usage-chart-update', ({ items }) => {
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
            chart.data.datasets[0].data = items[config.connection]['redis_network_usage']
            chart.update()
        })
    },
    labels(data) {
        return Object.keys(data['redis_network_usage'])
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
