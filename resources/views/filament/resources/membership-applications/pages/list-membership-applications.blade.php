<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Section --}}
        <div class="mb-6">
            @livewire('membership-applications-stats')
        </div>

        {{-- Table Section --}}
        <div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
