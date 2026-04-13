<x-filament-panels::page>
    <x-filament::form wire:submit="save">
        {{ $this->form }}

        <x-filament::form.actions
            :actions="$this->getCachedFormActions()"
        />
    </x-filament::form>
</x-filament-panels::page>