<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex justify-end pt-4">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Save Coop Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>