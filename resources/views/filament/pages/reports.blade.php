<x-filament-panels::page>
    @include('filament.reports.partials.styles')

    <div class="report-shell space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white px-6 py-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2">
                    <div class="inline-flex rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-emerald-700 dark:text-emerald-300">
                        Reports
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            Reports Dashboard
                        </h1>
                        <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">
                            Switch tabs to view each report. Filters stay in sync with the selected tab and the PDF export.
                        </p>
                    </div>
                </div>

                <div class="grid min-w-[280px] gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                    <div class="flex items-center justify-between gap-6">
                        <span class="text-slate-500 dark:text-slate-400">Active Report</span>
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $this->activeReportLabel() }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-6">
                        <span class="text-slate-500 dark:text-slate-400">Period</span>
                        <span class="font-medium text-slate-900 dark:text-white">
                            {{ \Illuminate\Support\Carbon::parse($this->startDate)->format('M d, Y') }} - {{ \Illuminate\Support\Carbon::parse($this->endDate)->format('M d, Y') }}
                        </span>
                    </div>
                    @if ($this->branchId)
                        <div class="flex items-center justify-between gap-6">
                            <span class="text-slate-500 dark:text-slate-400">Branch</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $this->branchOptions()[$this->branchId] ?? 'Selected branch' }}</span>
                        </div>
                    @endif
                    @if ($this->memberId)
                        <div class="flex items-center justify-between gap-6">
                            <span class="text-slate-500 dark:text-slate-400">Member</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $this->memberOptionLabel($this->memberId) ?? 'Selected member' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <p class="mt-5 text-sm text-slate-500 dark:text-slate-400">
                {{ $this->activeReportDescription() }}
            </p>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            {{ $this->form }}
        </section>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>