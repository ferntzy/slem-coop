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
            <div style="
                display: flex; flex-wrap: wrap; gap: 12px;
                background: linear-gradient(135deg, #f0fdfa, #e0f2fe);
                border: 1px solid #99f6e4;
                border-radius: 12px;
                padding: 14px 18px;
                margin-bottom: 16px;
            ">
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
                    <div style="display:flex; flex-direction:column; gap:2px; min-width:100px;">
                        <span style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; color:#6b7280;">
                            {{ $item['label'] }}
                        </span>
                        <span style="font-size:13px; font-weight:700; color:{{ ($item['highlight'] ?? false) ? '#0f766e' : '#111827' }};">
                            {{ $item['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Schedule table --}}
        @php $schedule = $this->getSchedule(); @endphp

        @if(empty($schedule))
            <div style="
                text-align:center; padding:3rem 1rem;
                color:#9ca3af; font-size:13px;
                background:#f9fafb; border-radius:12px;
                border: 1px dashed #e5e7eb;
            ">
                <div style="font-size:32px; margin-bottom:8px;">📋</div>
                <div style="font-weight:600; margin-bottom:4px;">No schedule found</div>
                <div style="font-size:12px;">Your loan payment schedule will appear here once a loan is active.</div>
            </div>
        @else
            {{-- Legend --}}
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
                @php
                    $legend = [
                        ['label' => 'Paid',           'bg' => '#dcfce7', 'color' => '#166534'],
                        ['label' => 'Partial',        'bg' => '#fef3c7', 'color' => '#92400e'],
                        ['label' => 'Late',           'bg' => '#fee2e2', 'color' => '#991b1b'],
                        ['label' => 'Partial / Late', 'bg' => '#fde68a', 'color' => '#92400e'],
                        ['label' => 'Unpaid',         'bg' => '#f3f4f6', 'color' => '#374151'],
                    ];
                @endphp
                @foreach($legend as $l)
                    <span style="
                        font-size:11px; font-weight:600; padding:3px 10px; border-radius:999px;
                        background:{{ $l['bg'] }}; color:{{ $l['color'] }};
                    ">{{ $l['label'] }}</span>
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
            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
                @php
                    $stats = [
                        ['label' => 'Total Periods',  'value' => $totalRows,  'color' => '#1e3a5f'],
                        ['label' => 'Paid',           'value' => $paidRows,   'color' => '#166534'],
                        ['label' => 'Remaining',      'value' => $unpaidRows, 'color' => '#92400e'],
                        ['label' => 'Late / Overdue', 'value' => $lateRows,   'color' => '#991b1b'],
                        ['label' => 'Total Penalty',  'value' => '₱' . number_format($totalPenalty, 2), 'color' => '#991b1b'],
                    ];
                @endphp
                @foreach($stats as $stat)
                    <div style="
                        padding: 10px 16px; border-radius:10px;
                        background:#f9fafb; border:1px solid #e5e7eb;
                        display:flex; flex-direction:column; gap:2px; min-width:90px;
                    ">
                        <span style="font-size:10px; color:#6b7280; font-weight:600; text-transform:uppercase; letter-spacing:0.4px;">
                            {{ $stat['label'] }}
                        </span>
                        <span style="font-size:16px; font-weight:700; color:{{ $stat['color'] }};">
                            {{ $stat['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Table --}}
            <div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:12px;">
                <table style="width:100%; border-collapse:collapse; min-width:680px;">
                    <thead>
                        <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                            @php
                                $headers = ['#', 'Due Date', 'Amortization', 'Penalty', 'Total Paid', 'Unpaid', 'Status'];
                            @endphp
                            @foreach($headers as $header)
                                <th style="
                                    padding: 10px 14px;
                                    text-align: {{ in_array($header, ['Amortization','Penalty','Total Paid','Unpaid']) ? 'right' : 'left' }};
                                    font-size: 10.5px; font-weight:700;
                                    text-transform:uppercase; letter-spacing:0.5px;
                                    color:#6b7280;
                                ">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedule as $row)
                            @php
                                $statusStyles = match($row['status']) {
                                    'Paid'           => ['bg' => '#dcfce7', 'color' => '#166534'],
                                    'Partial'        => ['bg' => '#fef3c7', 'color' => '#92400e'],
                                    'Late'           => ['bg' => '#fee2e2', 'color' => '#991b1b'],
                                    'Partial / Late' => ['bg' => '#fde68a', 'color' => '#92400e'],
                                    default          => ['bg' => '#f3f4f6', 'color' => '#374151'],
                                };

                                $rowBg = $row['status'] === 'Paid'
                                    ? 'background:#f0fdf4;'
                                    : ($row['status'] === 'Late' || $row['status'] === 'Partial / Late'
                                        ? 'background:#fff7f7;'
                                        : 'background:#ffffff;');

                                $isToday = \Carbon\Carbon::parse($row['due_date'])->isToday();
                                $isOverdue = \Carbon\Carbon::parse($row['due_date'])->isPast()
                                    && $row['status'] !== 'Paid';
                            @endphp
                            <tr style="{{ $rowBg }} border-bottom:1px solid #f3f4f6;">

                                {{-- Period --}}
                                <td style="padding:10px 14px; font-size:12px; font-weight:700; color:#1e3a5f;">
                                    {{ $row['period'] }}
                                </td>

                                {{-- Due Date --}}
                                <td style="padding:10px 14px; font-size:12px; color:{{ $isOverdue ? '#dc2626' : '#374151' }}; font-weight:{{ $isOverdue ? '700' : '500' }};">
                                    {{ \Carbon\Carbon::parse($row['due_date'])->format('M d, Y') }}
                                    @if($isToday)
                                        <span style="font-size:10px; background:#fef3c7; color:#92400e; padding:1px 6px; border-radius:999px; margin-left:4px; font-weight:700;">Today</span>
                                    @elseif($isOverdue)
                                        <span style="font-size:10px; background:#fee2e2; color:#991b1b; padding:1px 6px; border-radius:999px; margin-left:4px; font-weight:700;">Overdue</span>
                                    @endif
                                </td>

                                {{-- Amortization --}}
                                <td style="padding:10px 14px; font-size:12px; text-align:right; color:#111827; font-weight:600;">
                                    ₱{{ number_format($row['scheduled_amortization'], 2) }}
                                </td>

                                {{-- Penalty --}}
                                <td style="padding:10px 14px; font-size:12px; text-align:right; color:{{ $row['penalty'] > 0 ? '#dc2626' : '#9ca3af' }}; font-weight:{{ $row['penalty'] > 0 ? '600' : '400' }};">
                                    {{ $row['penalty'] > 0 ? '₱' . number_format($row['penalty'], 2) : '—' }}
                                </td>

                                {{-- Total Paid --}}
                                <td style="padding:10px 14px; font-size:12px; text-align:right; color:{{ $row['total_paid'] > 0 ? '#059669' : '#9ca3af' }}; font-weight:{{ $row['total_paid'] > 0 ? '600' : '400' }};">
                                    {{ $row['total_paid'] > 0 ? '₱' . number_format($row['total_paid'], 2) : '—' }}
                                </td>

                                {{-- Unpaid --}}
                                <td style="padding:10px 14px; font-size:12px; text-align:right; font-weight:700; color:{{ $row['unpaid_amount'] > 0 ? '#dc2626' : '#9ca3af' }};">
                                    {{ $row['unpaid_amount'] > 0 ? '₱' . number_format($row['unpaid_amount'], 2) : '—' }}
                                </td>

                                {{-- Status badge --}}
                                <td style="padding:10px 14px;">
                                    <span style="
                                        font-size:11px; font-weight:700;
                                        padding:3px 10px; border-radius:999px;
                                        background:{{ $statusStyles['bg'] }};
                                        color:{{ $statusStyles['color'] }};
                                    ">{{ $row['status'] }}</span>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer note --}}
            <p style="margin:10px 0 0; font-size:11px; color:#9ca3af; text-align:right;">
                Showing {{ $totalRows }} period(s) · Schedule is generated in real-time
            </p>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>