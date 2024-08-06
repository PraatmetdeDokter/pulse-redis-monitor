# Redis monitoring card for Laravel Pulse
A Laravel Pulse card for monitoring Redis keys, expiration times, and storage usage.

## Installation

First, install the package via composer:

```sh
composer require PraatmetdeDokter/pulse-redis-monitor
```

next, add the recorder to your `config/pulse.php`
```php
return [
    // ...

    'recorders' => [
        PraatmetdeDokter\Pulse\RedisMonitor\Recorders\RedisMonitorRecorder::class => [
            'connection' => env('PULSE_REDIS_MONITOR_CONNECTION', 'default'),
            'interval' => env('PULSE_REDIS_MONITOR_INTERVAL', 5) // Interval in minutes between monitoring events
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
The recorder uses the [pulse:check](https://laravel.com/docs/11.x/pulse#capturing-entries) command, so make its running.

# License
The MIT Licence (MIT). Please see the [license file](LICENSE) for more information.