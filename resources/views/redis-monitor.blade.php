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

        <livewire:graphs.memory :$colors :$period/>

        <livewire:graphs.active-keys :$colors :$period/>

        <livewire:graphs.removed-keys :$colors :$period/>

        <livewire:graphs.ttl :$colors :$period/>

        <livewire:graphs.network-usage :$colors :$period/>

        @endif
    </x-pulse::scroll>
</x-pulse::card>

