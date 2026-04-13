<x-filament-panels::page>
    @php
        $formatMoney = fn ($amount) => '₱' . number_format((float) $amount, 2);
        $accountsCount = $accounts?->count() ?? 0;
        $memberName = auth()->user()?->name ?? 'Member';
        $memberNumber = $memberDetail?->member_no;
        $membershipStatus = $memberDetail?->membershipStatus();
    @endphp

    {{-- Hero --}}
    <div
        style="
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            padding: 22px 26px;
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 55%, #0891b2 100%);
            margin-bottom: 16px;
        "
    >
        <div style="
            position: absolute; top: -60px; right: -60px;
            width: 220px; height: 220px; border-radius: 50%;
            background: rgba(255,255,255,0.08); pointer-events: none;
        "></div>
        <div style="
            position: absolute; bottom: -40px; left: 18%;
            width: 160px; height: 160px; border-radius: 50%;
            background: rgba(255,255,255,0.06); pointer-events: none;
        "></div>

        <div style="position: relative; display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap;">
            <div style="min-width: 240px;">
                <div style="
                    display:inline-flex; align-items:center; gap:8px;
                    background: rgba(255,255,255,0.14);
                    border: 1px solid rgba(255,255,255,0.22);
                    border-radius: 999px;
                    padding: 6px 14px;
                    margin-bottom: 10px;
                ">
                    <span style="font-size: 12px; font-weight: 700; color: rgba(255,255,255,0.92); letter-spacing: 0.3px;">
                        Member Savings
                    </span>
                </div>

                <h2 style="margin: 0 0 6px 0; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px;">
                    {{ $memberName }}
                </h2>
                <p style="margin: 0; font-size: 12.5px; color: rgba(255,255,255,0.7);">
                    @if($memberNumber)
                        Member No: <span style="font-weight: 700; color: rgba(255,255,255,0.9);">{{ $memberNumber }}</span>
                    @else
                        Your savings overview and latest activity
                    @endif
                    @if($membershipStatus)
                        &nbsp;&bull;&nbsp; <span style="font-weight: 700; color: rgba(255,255,255,0.9);">{{ $membershipStatus }}</span>
                    @endif
                </p>
            </div>

            <div style="
                display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap; justify-content:flex-end;
                min-width: 240px;
            ">
                <div style="
                    background: rgba(255,255,255,0.14);
                    border: 1px solid rgba(255,255,255,0.22);
                    border-radius: 14px;
                    padding: 12px 14px;
                    min-width: 190px;
                ">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.7);">
                        Total Savings Balance
                    </div>
                    <div style="margin-top: 4px; font-size: 22px; font-weight: 900; color: #ffffff;">
                        {{ $formatMoney($totalBalance ?? 0) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom: 16px;">
        <div style="flex: 1 1 220px;">
            <div style="border: 1px solid #e5e7eb; border-radius: 14px; padding: 14px 16px; background: #ffffff;">
                <div style="font-size: 11px; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: 0.08em;">
                    Savings Accounts
                </div>
                <div style="margin-top: 6px; font-size: 20px; font-weight: 900; color: #111827;">
                    {{ $accountsCount }}
                </div>
            </div>
        </div>

        <div style="flex: 1 1 220px;">
            <div style="border: 1px solid #e5e7eb; border-radius: 14px; padding: 14px 16px; background: #ffffff;">
                <div style="font-size: 11px; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: 0.08em;">
                    Share Capital
                </div>
                <div style="margin-top: 6px; font-size: 20px; font-weight: 900; color: #111827;">
                    {{ $formatMoney($memberDetail?->share_capital_balance ?? 0) }}
                </div>
            </div>
        </div>

        <div style="flex: 1 1 220px;">
            <div style="border: 1px solid #e5e7eb; border-radius: 14px; padding: 14px 16px; background: #ffffff;">
                <div style="font-size: 11px; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: 0.08em;">
                    Branch
                </div>
                <div style="margin-top: 6px; font-size: 14px; font-weight: 800; color: #111827;">
                    {{ $memberDetail?->branch?->name ?? '—' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Accounts + Transactions --}}
    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom: 16px;">
        <div style="flex: 1 1 520px; min-width: 320px;">
            <x-filament::section>
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                    <div>
                        <h3 style="margin:0; font-size:15px; font-weight:800; color: #111827;">Your Accounts</h3>
                        <p style="margin:4px 0 0; font-size:12px; color:#6b7280;">
                            Balances update as transactions are posted.
                        </p>
                    </div>
                </div>

                @if(($accounts?->count() ?? 0) === 0)
                    <div style="
                        text-align:center; padding:2.25rem 1rem;
                        color:#9ca3af; font-size:13px;
                        background:#f9fafb; border-radius:12px;
                        border: 1px dashed #e5e7eb;
                        margin-top: 14px;
                    ">
                        <div style="font-size:30px; margin-bottom:8px;">📌</div>
                        <div style="font-weight:700; margin-bottom:4px;">No savings accounts found</div>
                        <div style="font-size:12px;">If you think this is a mistake, please contact the coop staff.</div>
                    </div>
                @else
                    <div style="margin-top: 14px; overflow:auto; border: 1px solid #f3f4f6; border-radius: 12px;">
                        <table style="width:100%; border-collapse:collapse; min-width: 520px;">
                            <thead>
                                <tr style="background:#f9fafb; border-bottom:1px solid #f3f4f6;">
                                    <th style="text-align:left; padding:10px 14px; font-size:11px; letter-spacing:0.08em; text-transform:uppercase; color:#6b7280;">Account</th>
                                    <th style="text-align:left; padding:10px 14px; font-size:11px; letter-spacing:0.08em; text-transform:uppercase; color:#6b7280;">Type</th>
                                    <th style="text-align:right; padding:10px 14px; font-size:11px; letter-spacing:0.08em; text-transform:uppercase; color:#6b7280;">Balance</th>
                                    <th style="text-align:right; padding:10px 14px; font-size:11px; letter-spacing:0.08em; text-transform:uppercase; color:#6b7280;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounts as $account)
                                    @php
                                        $status = (string) ($account->status ?? '—');
                                        $statusStyles = match ($status) {
                                            'Approved' => ['bg' => '#dcfce7', 'color' => '#166534'],
                                            'Pending' => ['bg' => '#fef3c7', 'color' => '#92400e'],
                                            'Rejected' => ['bg' => '#fee2e2', 'color' => '#991b1b'],
                                            default => ['bg' => '#f3f4f6', 'color' => '#374151'],
                                        };
                                    @endphp
                                    <tr style="border-bottom:1px solid #f3f4f6;">
                                        <td style="padding:10px 14px; font-size:12px; font-weight:800; color:#111827;">
                                            {{ $account->account_number ?? '—' }}
                                        </td>
                                        <td style="padding:10px 14px; font-size:12px; color:#374151;">
                                            {{ $account->savingsType?->name ?? '—' }}
                                        </td>
                                        <td style="padding:10px 14px; font-size:12px; font-weight:900; color:#0f766e; text-align:right;">
                                            {{ $formatMoney($account->balance ?? 0) }}
                                        </td>
                                        <td style="padding:10px 14px; text-align:right;">
                                            <span style="
                                                font-size:11px; font-weight:800;
                                                padding:3px 10px; border-radius:999px;
                                                background:{{ $statusStyles['bg'] }};
                                                color:{{ $statusStyles['color'] }};
                                            ">{{ $status }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-filament::section>
        </div>

        <div style="flex: 1 1 420px; min-width: 320px;">
            <x-filament::section>
                <h3 style="margin:0; font-size:15px; font-weight:800; color: #111827;">Recent Transactions</h3>
                <p style="margin:4px 0 0; font-size:12px; color:#6b7280;">
                    Latest posted savings activity.
                </p>

                @if(($recentTransactions?->count() ?? 0) === 0)
                    <div style="
                        text-align:center; padding:1.75rem 1rem;
                        color:#9ca3af; font-size:13px;
                        background:#f9fafb; border-radius:12px;
                        border: 1px dashed #e5e7eb;
                        margin-top: 14px;
                    ">
                        <div style="font-size:28px; margin-bottom:8px;">🧾</div>
                        <div style="font-weight:700; margin-bottom:4px;">No transactions yet</div>
                        <div style="font-size:12px;">Your deposits and withdrawals will appear here.</div>
                    </div>
                @else
                    <div style="margin-top: 14px; display:flex; flex-direction:column; gap:10px;">
                        @foreach($recentTransactions as $txn)
                            @php
                                $direction = strtoupper((string) ($txn->direction ?? ''));
                                $isIn = in_array($direction, ['IN', 'DEPOSIT', 'CREDIT'], true);
                                $badgeBg = $isIn ? '#dcfce7' : '#fee2e2';
                                $badgeColor = $isIn ? '#166534' : '#991b1b';
                                $txnLabel = trim(implode(' · ', array_filter([
                                    $txn->type,
                                    $txn->savingsAccount?->savingsType?->name,
                                ], fn ($v) => filled($v))));
                            @endphp

                            <div style="
                                display:flex; justify-content:space-between; gap:10px;
                                border: 1px solid #f3f4f6; border-radius: 12px; padding: 10px 12px;
                                background: #ffffff;
                            ">
                                <div style="min-width: 0;">
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <span style="
                                            font-size:11px; font-weight:900; letter-spacing:0.08em; text-transform:uppercase;
                                            padding:3px 10px; border-radius:999px;
                                            background:{{ $badgeBg }}; color:{{ $badgeColor }};
                                        ">
                                            {{ $isIn ? 'Deposit' : 'Withdrawal' }}
                                        </span>
                                        <span style="font-size:12px; font-weight:800; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width: 280px;">
                                            {{ $txnLabel !== '' ? $txnLabel : 'Savings Transaction' }}
                                        </span>
                                    </div>
                                    <div style="margin-top: 4px; font-size: 12px; color:#6b7280;">
                                        {{ $txn->transaction_date?->format('M d, Y') ?? '—' }}
                                        @if($txn->reference_no)
                                            &nbsp;&bull;&nbsp; Ref: <span style="font-weight: 700; color:#374151;">{{ $txn->reference_no }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div style="text-align:right; flex-shrink:0;">
                                    <div style="font-size:12px; font-weight:900; color: {{ $isIn ? '#059669' : '#dc2626' }};">
                                        {{ $isIn ? '+' : '-' }}{{ $formatMoney($txn->amount ?? 0) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>
    </div>

    {{-- Full Filament table (filtered to your records) --}}
    <x-filament::section>
        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
