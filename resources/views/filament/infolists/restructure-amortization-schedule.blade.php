@php
    $record = $getRecord();

    $loanAccount = \App\Models\LoanAccount::where('loan_application_id', $record->loan_application_id)
        ->where('status', 'Active')
        ->latest()
        ->first();

    $schedule = $loanAccount
        ? app(\App\Services\LoanAmortizationService::class)->generate(
            loanAmount: (float) $record->new_principal,
            monthlyInterestRatePercent: (float) $record->new_interest,
            termMonths: (int) $record->term_months,
            releaseDate: $loanAccount->release_date,
        )
        : [];
@endphp

@if (! $schedule)
    <div style="font-size: 14px; color: #6b7280; padding: 12px 0;">
        No amortization schedule available.
    </div>
@else
    <div style="overflow-x: auto; border: 1px solid #d1fae5; border-radius: 16px; background: #ffffff;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: #ecfdf5;">
                    <th style="padding: 12px 14px; text-align: left; font-weight: 700; border-bottom: 1px solid #d1fae5;">#</th>
                    <th style="padding: 12px 14px; text-align: left; font-weight: 700; border-bottom: 1px solid #d1fae5;">Due Date</th>
                    <th style="padding: 12px 14px; text-align: right; font-weight: 700; border-bottom: 1px solid #d1fae5;">Interest</th>
                    <th style="padding: 12px 14px; text-align: right; font-weight: 700; border-bottom: 1px solid #d1fae5;">Principal</th>
                    <th style="padding: 12px 14px; text-align: right; font-weight: 700; border-bottom: 1px solid #d1fae5;">Amortization</th>
                    <th style="padding: 12px 14px; text-align: right; font-weight: 700; border-bottom: 1px solid #d1fae5;">Penalty</th>
                    <th style="padding: 12px 14px; text-align: right; font-weight: 700; border-bottom: 1px solid #d1fae5;">Paid</th>
                    <th style="padding: 12px 14px; text-align: right; font-weight: 700; border-bottom: 1px solid #d1fae5;">Unpaid</th>
                    <th style="padding: 12px 14px; text-align: left; font-weight: 700; border-bottom: 1px solid #d1fae5;">Status</th>
                    <th style="padding: 12px 14px; text-align: right; font-weight: 700; border-bottom: 1px solid #d1fae5;">Balance</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($schedule as $row)
                    @php
                        $penalty = 0;
                        $paid = 0;

                        $totalDue = (float) $row['amortization'] + $penalty;
                        $unpaid = $totalDue - $paid;

                        $status = $unpaid <= 0 ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid');

                        $badgeStyle = match ($status) {
                            'Paid' => 'background:#dcfce7;color:#166534;',
                            'Partial' => 'background:#fef3c7;color:#92400e;',
                            default => 'background:#fee2e2;color:#991b1b;',
                        };

                        $rowBg = $loop->odd ? '#ffffff' : '#f9fffc';
                    @endphp

                    <tr style="background: {{ $rowBg }};">
                        <td style="padding: 12px 14px; border-bottom: 1px solid #eef2f7; white-space: nowrap;">
                            {{ $row['month'] ?? $loop->iteration }}
                        </td>

                        <td style="padding: 12px 14px; border-bottom: 1px solid #eef2f7; white-space: nowrap;">
                            {{ $row['due_date'] }}
                        </td>

                        <td style="padding: 12px 14px; border-bottom: 1px solid #eef2f7; text-align: right; white-space: nowrap;">
                            ₱{{ number_format((float) $row['interest'], 2) }}
                        </td>

                        <td style="padding: 12px 14px; border-bottom: 1px solid #eef2f7; text-align: right; white-space: nowrap;">
                            ₱{{ number_format((float) $row['principal'], 2) }}
                        </td>

                        <td style="padding: 12px 14px; border-bottom: 1px solid #eef2f7; text-align: right; white-space: nowrap; font-weight: 600; color: #111827;">
                            ₱{{ number_format((float) $row['amortization'], 2) }}
                        </td>

                        <td style="padding: 12px 14px; border-bottom: 1px solid #eef2f7; text-align: right; white-space: nowrap; color: #e11d48; font-weight: 700;">
                            ₱{{ number_format((float) $penalty, 2) }}
                        </td>

                        <td style="padding: 12px 14px; border-bottom: 1px solid #eef2f7; text-align: right; white-space: nowrap; color: #059669; font-weight: 600;">
                            ₱{{ number_format((float) $paid, 2) }}
                        </td>

                        <td style="padding: 12px 14px; border-bottom: 1px solid #eef2f7; text-align: right; white-space: nowrap; color: #d97706; font-weight: 600;">
                            ₱{{ number_format((float) $unpaid, 2) }}
                        </td>

                        <td style="padding: 12px 14px; border-bottom: 1px solid #eef2f7; white-space: nowrap;">
                            <span style="display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; {{ $badgeStyle }}">
                                {{ $status }}
                            </span>
                        </td>

                        <td style="padding: 12px 14px; border-bottom: 1px solid #eef2f7; text-align: right; white-space: nowrap;">
                            ₱{{ number_format((float) $row['balance'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif