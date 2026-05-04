@php
    $transactions = collect($getState() ?? []);

    $formatCurrency = static fn ($value, string $placeholder = '-') => $value !== null
        ? 'PHP '.number_format((float) $value, 2)
        : $placeholder;

    $formatDate = static fn ($value, string $placeholder = '-') => $value
        ? \Illuminate\Support\Carbon::parse($value)->format('M d, Y h:i A')
        : $placeholder;

    $formatSimpleDate = static fn ($value, string $placeholder = '-') => $value
        ? \Illuminate\Support\Carbon::parse($value)->format('M d, Y')
        : $placeholder;

    $formatMaturityAction = static fn ($value) => match ($value) {
        'renew_time_deposit' => 'Re-Time Deposit',
        'transfer_to_savings' => 'Transfer to Regular Savings',
        default => 'Auto-transfer to Regular Savings',
    };

    $statusColors = [
        'ongoing' => '#ca8a04',
        'completed' => '#16a34a',
        'withdrawn' => '#6b7280',
    ];
@endphp

<div style="width: 100%; max-width: 100%; overflow-x: auto;">
    <div style="min-width: 107rem;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 10rem; padding: 12px; text-align: left; white-space: nowrap;">Amount</th>
                    <th style="width: 8rem; padding: 12px; text-align: left; white-space: nowrap;">Term</th>
                    <th style="width: 9rem; padding: 12px; text-align: left; white-space: nowrap;">Status</th>
                    <th style="width: 14rem; padding: 12px; text-align: left; white-space: nowrap;">Maturity Option</th>
                    <th style="width: 12rem; padding: 12px; text-align: left; white-space: nowrap;">Deposit Date</th>
                    <th style="width: 10rem; padding: 12px; text-align: left; white-space: nowrap;">Maturity Date</th>
                    <th style="width: 14rem; padding: 12px; text-align: left; white-space: nowrap;">Transferred to Regular Savings</th>
                    <th style="width: 12rem; padding: 12px; text-align: left; white-space: nowrap;">Transfer Date</th>
                    <th style="width: 18rem; padding: 12px; text-align: left; white-space: nowrap;">Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr style="border-top: 1px solid rgba(156, 163, 175, 0.18);">
                        <td style="padding: 14px 12px; white-space: nowrap;">{{ $formatCurrency($transaction['amount'] ?? null) }}</td>
                        <td style="padding: 14px 12px; white-space: nowrap;">{{ filled($transaction['terms'] ?? null) ? ($transaction['terms'].' month(s)') : '-' }}</td>
                        <td style="padding: 14px 12px; white-space: nowrap;">
                            <span style="display: inline-block; border: 1px solid {{ $statusColors[$transaction['status'] ?? ''] ?? '#6b7280' }}; color: {{ $statusColors[$transaction['status'] ?? ''] ?? '#6b7280' }}; border-radius: 0.5rem; padding: 0.125rem 0.5rem; font-size: 0.875rem;">
                                {{ ucfirst((string) ($transaction['status'] ?? 'unknown')) }}
                            </span>
                        </td>
                        <td style="padding: 14px 12px; white-space: nowrap;">{{ $formatMaturityAction($transaction['maturity_action'] ?? null) }}</td>
                        <td style="padding: 14px 12px; white-space: nowrap;">{{ $formatDate($transaction['transaction_date'] ?? null) }}</td>
                        <td style="padding: 14px 12px; white-space: nowrap;">{{ $formatSimpleDate($transaction['maturity_date'] ?? null) }}</td>
                        <td style="padding: 14px 12px; white-space: nowrap;">{{ $formatCurrency($transaction['transferred_amount'] ?? null, 'Not transferred yet') }}</td>
                        <td style="padding: 14px 12px; white-space: nowrap;">{{ $formatDate($transaction['transfer_date'] ?? null) }}</td>
                        <td style="padding: 14px 12px; min-width: 18rem;">{{ $transaction['notes'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding: 14px 12px;">No time deposit transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
