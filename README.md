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
        PraatmetdeDokter\Pulse\RedisMonitor\Recorders\RedisMonitor::class => [
            'enabled' => env('PULSE_VALIDATION_ERRORS_ENABLED', true),
            'sample_rate' => env('PULSE_VALIDATION_ERRORS_SAMPLE_RATE', 1)
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