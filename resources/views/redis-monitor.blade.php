<x-pulse::card id="redis-monitor-card" :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header
        name="Redis"
        title="Time: {{ number_format($time) }}ms; Run at: {{ $runAt }};"
        details="past {{ $this->periodForHumans() }}"
    >
        <x-slot:icon>
            <x-pulse::icons.circle-stack />
        </x-slot:icon>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        @if ($errors->isEmpty())
            <x-pulse::no-results />
        @else
            <!-- Add card here -->
        @endif
    </x-pulse::scroll>
</x-pulse::card>