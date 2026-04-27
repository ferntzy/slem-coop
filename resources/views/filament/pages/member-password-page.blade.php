<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-2xl border border-green-100 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-slate-900">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Member Password Update</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                Update the temporary password sent to your email after approval.
            </p>
        </div>

        {{ $this->form }}
    </div>
</x-filament-panels::page>