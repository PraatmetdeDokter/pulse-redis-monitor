# Redis monitoring card for Laravel Pulse
A customizable Laravel Pulse card for monitoring key Redis metrics.

## Features

This card monitors and displays the following Redis metrics:

- **Memory usage:**
  - **Used memory:** The current amount of memory being utilized by Redis.
  - **Max memory:** The maximum memory available to Redis.

- **Key statistics:**
  - **Total keys:** The total number of keys currently stored in Redis.
  - **Keys with expiration:** A count of keys that have a set expiration time.

- **Removed keys:**
  - **Expired keys:** The number of keys that have been automatically removed after their TTL has expired.
  - **Evicted keys:** The number of keys evicted due to memory constraints when Redis is running out of space.

- **Average TTL:** The average remaining Time to Live (TTL) of keys in Redis.

- **Network usage:**
  - **Total Traffic:** The sum of data received and sent by Redis (Traffic In + Traffic Out).


## Installation

First, install the package via composer:

```sh
composer require praatmetdedokter/pulse-redis-monitor
```

next, add the recorder to your `config/pulse.php`
```php
return [
    // ...

    'recorders' => [
        PraatmetdeDokter\Pulse\RedisMonitor\Recorders\RedisMonitorRecorder::class => [
            'connections' => env('PULSE_REDIS_MONITOR_CONNECTIONS', ['default']),
            'interval' => env('PULSE_REDIS_MONITOR_INTERVAL', 5), // Interval in minutes between monitoring events
            'colors' => [
                'primary' => '#ee3969',
                'secondary' => '#2ca3cc'
            ],
            'metrics' => [
                'memory_usage' => env('PULSE_REDIS_MONITOR_MEMORY_USAGE', true),
                'key_statistics' => env('PULSE_REDIS_MONITOR_KEY_STATISTICS', true),
                'removed_keys' => env('PULSE_REDIS_MONITOR_REMOVED_KEYS', true),
                'network_usage' => env('PULSE_REDIS_MONITOR_NETWORK_USAGE', true),
            ]
        ],

        // ...
    ],
];
```

Next, add the card to your `resources/views/vendor/pulse/dashboard.blade.php`:

```blade
<x-pulse>
    <livewire:pulse.redis-monitor/>

    <!-- ... -->
</x-pulse>
```

### Usage
Don't forget to run the [pulse:check](https://laravel.com/docs/11.x/pulse#capturing-entries) command to start recording.

# Configuration
To customize the behavior of the Redis Monitor Recorder, you can optionally include `colors` and `metrics` settings in your configuration. You only need to specify the values you want to change from the default settings.

## Configruation options
- `colors`: Define custom colors for the charts.
- `metrics`: Disable specific metrics.

# License
The MIT Licence (MIT). Please see the [license file](LICENSE) for more information.
