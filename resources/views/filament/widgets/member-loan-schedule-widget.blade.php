<x-filament-widgets::widget>
    <x-filament::section>

        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
            <div>
                <h3 style="margin:0; font-size:15px; font-weight:700; color:var(--color-text-primary);">
                    Loan Payment Schedule
                </h3>
                <p style="margin:4px 0 0; font-size:12px; color:var(--color-text-secondary);">
                    Full amortization breakdown for your active loans
                </p>
            </div>

            {{-- Loan selector tabs --}}
            @php $loans = $this->getLoans(); @endphp
            @if($loans->count() > 1)
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    @foreach($loans as $loan)
                        @php $isActive = $this->selectedLoanId === $loan->loan_account_id; @endphp
                        <button
                            wire:click="selectLoan({{ $loan->loan_account_id }})"
                            style="
                                padding: 6px 14px;
                                border-radius: 8px;
                                font-size: 12px;
                                font-weight: 600;
                                cursor: pointer;
                                border: 1.5px solid {{ $isActive ? '#0d9488' : '#e5e7eb' }};
                                background: {{ $isActive ? '#0d9488' : 'transparent' }};
                                color: {{ $isActive ? '#ffffff' : '#6b7280' }};
                                transition: all 0.15s;
                            "
                        >
                            LA-{{ str_pad($loan->loan_account_id, 5, '0', STR_PAD_LEFT) }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Selected loan summary bar --}}
        @php $selectedLoan = $this->getSelectedLoan(); @endphp
        @if($selectedLoan)
            <div class="mb-4 flex flex-wrap gap-3 rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-sky-50 px-4 py-3.5 dark:border-emerald-900/60 dark:from-zinc-900 dark:to-zinc-800">
                @php
                    $summaryItems = [
                        ['label' => 'Loan #',            'value' => 'LA-' . str_pad($selectedLoan->loan_account_id, 5, '0', STR_PAD_LEFT)],
                        ['label' => 'Principal',         'value' => '₱' . number_format($selectedLoan->principal_amount, 2)],
                        ['label' => 'Monthly Amort.',    'value' => '₱' . number_format($selectedLoan->monthly_amortization, 2)],
                        ['label' => 'Term',              'value' => $selectedLoan->term_months . ' months'],
                        ['label' => 'Remaining Balance', 'value' => '₱' . number_format($selectedLoan->balance, 2), 'highlight' => true],
                        ['label' => 'Maturity Date',     'value' => $selectedLoan->maturity_date?->format('M d, Y') ?? '—'],
                        ['label' => 'Status',            'value' => $selectedLoan->status],
                    ];
                @endphp
                @foreach($summaryItems as $item)
                    <div class="flex min-w-[100px] flex-col gap-0.5">
                        <span class="text-[10px] font-semibold uppercase tracking-[0.5px] text-gray-500 dark:text-zinc-400">
                            {{ $item['label'] }}
                        </span>
                        <span class="text-[13px] font-bold {{ ($item['highlight'] ?? false) ? 'text-teal-700 dark:text-teal-300' : 'text-gray-900 dark:text-zinc-100' }}">
                            {{ $item['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Schedule table --}}
        @php $schedule = $this->getSchedule(); @endphp

        @if(empty($schedule))
            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-12 text-center text-sm text-gray-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                <div style="font-size:32px; margin-bottom:8px;">📋</div>
                <div style="font-weight:600; margin-bottom:4px;">No schedule found</div>
                <div style="font-size:12px;">Your loan payment schedule will appear here once a loan is active.</div>
            </div>
        @else
            {{-- Legend --}}
            <div class="mb-3 flex flex-wrap gap-2">
                @php
                    $legend = [
                        ['label' => 'Paid',           'classes' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300'],
                        ['label' => 'Partial',        'classes' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300'],
                        ['label' => 'Late',           'classes' => 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300'],
                        ['label' => 'Partial / Late', 'classes' => 'bg-amber-200 text-amber-900 dark:bg-amber-500/30 dark:text-amber-300'],
                        ['label' => 'Unpaid',         'classes' => 'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-zinc-200'],
                    ];
                @endphp
                @foreach($legend as $l)
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $l['classes'] }}">{{ $l['label'] }}</span>
                @endforeach
            </div>

            {{-- Stats row --}}
            @php
                $totalRows    = count($schedule);
                $paidRows     = collect($schedule)->where('status', 'Paid')->count();
                $lateRows     = collect($schedule)->whereIn('status', ['Late', 'Partial / Late'])->count();
                $unpaidRows   = collect($schedule)->whereIn('status', ['Unpaid', 'Partial'])->count();
                $totalPenalty = collect($schedule)->sum('penalty');
            @endphp
            <div class="mb-4 flex flex-wrap gap-2.5">
                @php
                    $stats = [
                        ['label' => 'Total Periods',  'value' => $totalRows,  'classes' => 'text-slate-700 dark:text-sky-300'],
                        ['label' => 'Paid',           'value' => $paidRows,   'classes' => 'text-emerald-700 dark:text-emerald-300'],
                        ['label' => 'Remaining',      'value' => $unpaidRows, 'classes' => 'text-amber-700 dark:text-amber-300'],
                        ['label' => 'Late / Overdue', 'value' => $lateRows,   'classes' => 'text-rose-700 dark:text-rose-300'],
                        ['label' => 'Total Penalty',  'value' => '₱' . number_format($totalPenalty, 2), 'classes' => 'text-rose-700 dark:text-rose-300'],
                    ];
                @endphp
                @foreach($stats as $stat)
                    <div class="flex min-w-[90px] flex-col gap-0.5 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-zinc-700 dark:bg-zinc-900">
                        <span class="text-[10px] font-semibold uppercase tracking-[0.4px] text-gray-500 dark:text-zinc-400">
                            {{ $stat['label'] }}
                        </span>
                        <span class="text-base font-bold {{ $stat['classes'] }}">
                            {{ $stat['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-black">
                <table class="w-full min-w-[680px] border-collapse text-sm text-gray-700 dark:text-zinc-100">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-zinc-700 dark:bg-zinc-950">
                            @php
                                $headers = ['#', 'Due Date', 'Amortization', 'Penalty', 'Total Paid', 'Unpaid', 'Status'];
                            @endphp
                            @foreach($headers as $header)
                                <th
                                    class="px-3.5 py-2.5 text-[10.5px] font-bold uppercase tracking-[0.5px] text-gray-500 dark:text-zinc-300 {{ in_array($header, ['Amortization','Penalty','Total Paid','Unpaid']) ? 'text-right' : 'text-left' }}"
                                >{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedule as $row)
                            @php
                                $statusBadgeClasses = match($row['status']) {
                                    'Paid'           => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300',
                                    'Partial'        => 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300',
                                    'Late'           => 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300',
                                    'Partial / Late' => 'bg-amber-200 text-amber-900 dark:bg-amber-500/30 dark:text-amber-300',
                                    default          => 'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-zinc-200',
                                };

                                $rowClasses = $row['status'] === 'Paid'
                                    ? 'bg-emerald-50/60 dark:bg-emerald-950/20'
                                    : ($row['status'] === 'Late' || $row['status'] === 'Partial / Late'
                                        ? 'bg-rose-50/70 dark:bg-rose-950/20'
                                        : 'bg-white dark:bg-black');

                                $isToday = \Carbon\Carbon::parse($row['due_date'])->isToday();
                                $isOverdue = \Carbon\Carbon::parse($row['due_date'])->isPast()
                                    && $row['status'] !== 'Paid';

                                $dueDateClasses = $isOverdue
                                    ? 'text-rose-600 dark:text-rose-300 font-bold'
                                    : 'text-gray-700 dark:text-zinc-200 font-medium';

                                $penaltyClasses = $row['penalty'] > 0
                                    ? 'text-rose-600 dark:text-rose-300 font-semibold'
                                    : 'text-gray-400 dark:text-zinc-500 font-normal';

                                $paidClasses = $row['total_paid'] > 0
                                    ? 'text-emerald-600 dark:text-emerald-300 font-semibold'
                                    : 'text-gray-400 dark:text-zinc-500 font-normal';

                                $unpaidClasses = $row['unpaid_amount'] > 0
                                    ? 'text-rose-600 dark:text-rose-300'
                                    : 'text-gray-400 dark:text-zinc-500';
                            @endphp
                            <tr class="{{ $rowClasses }} border-b border-gray-100 dark:border-zinc-800">

                                {{-- Period --}}
                                <td class="px-3.5 py-2.5 text-xs font-bold text-slate-700 dark:text-zinc-200">
                                    {{ $row['period'] }}
                                </td>

                                {{-- Due Date --}}
                                <td class="px-3.5 py-2.5 text-xs {{ $dueDateClasses }}">
                                    {{ \Carbon\Carbon::parse($row['due_date'])->format('M d, Y') }}
                                    @if($isToday)
                                        <span class="ml-1 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">Today</span>
                                    @elseif($isOverdue)
                                        <span class="ml-1 rounded-full bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-800 dark:bg-rose-500/20 dark:text-rose-300">Overdue</span>
                                    @endif
                                </td>

                                {{-- Amortization --}}
                                <td class="px-3.5 py-2.5 text-right text-xs font-semibold text-gray-900 dark:text-zinc-100">
                                    ₱{{ number_format($row['scheduled_amortization'], 2) }}
                                </td>

                                {{-- Penalty --}}
                                <td class="px-3.5 py-2.5 text-right text-xs {{ $penaltyClasses }}">
                                    {{ $row['penalty'] > 0 ? '₱' . number_format($row['penalty'], 2) : '—' }}
                                </td>

                                {{-- Total Paid --}}
                                <td class="px-3.5 py-2.5 text-right text-xs {{ $paidClasses }}">
                                    {{ $row['total_paid'] > 0 ? '₱' . number_format($row['total_paid'], 2) : '—' }}
                                </td>

                                {{-- Unpaid --}}
                                <td class="px-3.5 py-2.5 text-right text-xs font-bold {{ $unpaidClasses }}">
                                    {{ $row['unpaid_amount'] > 0 ? '₱' . number_format($row['unpaid_amount'], 2) : '—' }}
                                </td>

                                {{-- Status badge --}}
                                <td class="px-3.5 py-2.5">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusBadgeClasses }}">{{ $row['status'] }}</span>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer note --}}
            <p class="mt-2.5 text-right text-[11px] text-gray-400 dark:text-zinc-500">
                Showing {{ $totalRows }} period(s) · Schedule is generated in real-time
            </p>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>