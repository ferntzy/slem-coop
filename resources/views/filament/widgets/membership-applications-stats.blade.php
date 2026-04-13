<x-filament-widgets::widget>
    @php
        $stats = $this->getStats();
    @endphp

    <div class="space-y-4">

        {{-- Header Banner (mirrors Loan Management Dashboard card) --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-50 to-white border border-green-100 p-6 flex items-start justify-between">
            <div>
                <span class="inline-block mb-3 rounded-full border border-green-300 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-green-700">
                    Membership Management Dashboard
                </span>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Membership Applications Overview</h2>
            </div>
            <div class="text-right shrink-0 ml-6 rounded-xl bg-white border border-green-100 shadow-sm px-6 py-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Total Applications</p>
                <p class="text-4xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-400 mt-1">All time</p>
            </div>
        </div>

        {{-- Status Cards (mirrors the 4 colored cards) --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

            {{-- Pending --}}
            <div class="rounded-xl border-l-4 border-yellow-400 bg-yellow-50 p-5 shadow-[0_14px_30px_rgba(25,74,44,0.08)] dark:shadow-[0_18px_40px_rgba(0,0,0,0.28)] transition-all hover:-translate-y-0.5">
                <p class="text-xs font-semibold uppercase tracking-widest text-yellow-600 mb-2">Pending</p>
                <p class="text-4xl font-bold text-gray-800 mb-2">{{ $stats['pending'] }}</p>
                <p class="text-xs text-gray-500">Applications awaiting initial review.</p>
            </div>

            {{-- Under Review / Needs Review --}}
            <div class="rounded-xl border-l-4 border-blue-400 bg-blue-50 p-5 shadow-[0_14px_30px_rgba(25,74,44,0.08)] dark:shadow-[0_18px_40px_rgba(0,0,0,0.28)] transition-all hover:-translate-y-0.5">
                <p class="text-xs font-semibold uppercase tracking-widest text-blue-600 mb-2">Needs Review</p>
                <p class="text-4xl font-bold text-gray-800 mb-2">{{ $stats['under_review'] }}</p>
                <p class="text-xs text-gray-500">Applications currently being evaluated.</p>
            </div>

            {{-- Approved --}}
            <div class="rounded-xl border-l-4 border-green-500 bg-green-50 p-5 shadow-[0_14px_30px_rgba(25,74,44,0.08)] dark:shadow-[0_18px_40px_rgba(0,0,0,0.28)] transition-all hover:-translate-y-0.5">
                <p class="text-xs font-semibold uppercase tracking-widest text-green-600 mb-2">Approved</p>
                <p class="text-4xl font-bold text-gray-800 mb-2">{{ $stats['approved'] }}</p>
                <p class="text-xs text-gray-500">Applications approved for membership.</p>
            </div>

            {{-- Rejected --}}
            <div class="rounded-xl border-l-4 border-[#b04b4b] bg-gradient-to-br from-[#fff8f8] to-[#f9efef] dark:from-[rgba(244,63,94,0.10)] dark:to-[rgba(2,6,23,0.35)] dark:border-[rgba(244,63,94,0.55)] p-5 shadow-[0_14px_30px_rgba(25,74,44,0.08)] dark:shadow-[0_18px_40px_rgba(0,0,0,0.28)] transition-all hover:-translate-y-0.5">
                <p class="text-xs font-semibold uppercase tracking-widest text-[#a33d3d] dark:text-[#f0a3a3] mb-2">Rejected</p>
                <p class="text-4xl font-bold text-gray-800 dark:text-[#f3f7f4] mb-2">{{ $stats['rejected'] }}</p>
                <p class="text-xs text-gray-500 dark:text-[#a7b8ad]">Applications that did not pass evaluation.</p>
            </div>

        </div>

        {{-- Quick Insights --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

            <div class="col-span-2 rounded-xl border border-gray-200 dark:border-[rgba(148,163,184,0.14)] bg-white dark:bg-[rgba(2,6,23,0.35)] p-5 shadow-[0_14px_30px_rgba(25,74,44,0.08)] dark:shadow-[0_18px_40px_rgba(0,0,0,0.28)] transition-all hover:-translate-y-0.5">
                <p class="text-base font-semibold text-gray-700 dark:text-[#f3f7f4] mb-4">Quick Insights</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-lg bg-gray-50 dark:bg-[rgba(2,6,23,0.25)] border border-gray-200 dark:border-[rgba(148,163,184,0.14)] p-3">
                        <p class="text-xs uppercase tracking-widest text-gray-400 dark:text-[#a7b8ad] mb-1">This Month</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-[#f3f7f4]">
                            {{ \App\Models\MembershipApplication::whereMonth('created_at', now()->month)->count() }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-[rgba(2,6,23,0.25)] border border-gray-200 dark:border-[rgba(148,163,184,0.14)] p-3">
                        <p class="text-xs uppercase tracking-widest text-gray-400 dark:text-[#a7b8ad] mb-1">Approval Rate</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-[#f3f7f4]">
                            {{ $stats['total'] > 0 ? round(($stats['approved'] / $stats['total']) * 100) : 0 }}%
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-gradient-to-br from-[#f1faf3] to-[#e7f6ea] dark:from-[rgba(34,197,94,0.12)] dark:to-[rgba(2,6,23,0.35)] border border-[#cfe5d5] dark:border-[rgba(34,197,94,0.18)] p-5 shadow-[0_14px_30px_rgba(25,74,44,0.08)] dark:shadow-[0_18px_40px_rgba(0,0,0,0.28)] transition-all hover:-translate-y-0.5">
                <p class="text-xs font-semibold uppercase tracking-widest text-[#166534] dark:text-[#86efac] mb-1">Open Pipeline</p>
                <p class="text-4xl font-bold text-gray-800 dark:text-[#f3f7f4]">{{ $stats['pending'] + $stats['under_review'] }}</p>
                <p class="text-xs text-gray-500 dark:text-[#a7b8ad] mt-2">Applications still in active processing stages.</p>
            </div>

            <div class="rounded-xl bg-gradient-to-br from-[#fcfbf4] to-[#f5f1e4] dark:from-[rgba(245,158,11,0.10)] dark:to-[rgba(2,6,23,0.35)] border border-[#e3ddc1] dark:border-[rgba(245,158,11,0.18)] p-5 shadow-[0_14px_30px_rgba(25,74,44,0.08)] dark:shadow-[0_18px_40px_rgba(0,0,0,0.28)] transition-all hover:-translate-y-0.5">
                <p class="text-xs font-semibold uppercase tracking-widest text-[#8b7a2f] dark:text-[#f3d98b] mb-1">Decisioned</p>
                <p class="text-4xl font-bold text-gray-800 dark:text-[#f3f7f4]">{{ $stats['approved'] + $stats['rejected'] }}</p>
                <p class="text-xs text-gray-500 dark:text-[#a7b8ad] mt-2">Applications with final outcomes recorded.</p>
            </div>

        </div>

    </div>
</x-filament-widgets::widget>
