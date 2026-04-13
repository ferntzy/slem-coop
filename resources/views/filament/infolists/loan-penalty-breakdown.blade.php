@php
    /** @var \App\Models\LoanApplication|\App\Models\LoanAccount $record */

    $loanAccount = $record instanceof \App\Models\LoanAccount
        ? $record
        : $record->loanAccount;

    $breakdown = $loanAccount
        ? app(\App\Services\LoanPenaltyService::class)->calculateForLoanAccount($loanAccount)
        : null;
@endphp

<div class="space-y-6">
    @if (! $loanAccount)
        <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-600 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            No loan account found yet.
        </div>
    @elseif (! $loanAccount->penalty_rule_id)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 shadow-sm dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
            No penalty rule assigned to this loan account.
        </div>
    @elseif (blank($breakdown['rows'] ?? []))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
            No overdue installments with penalty as of {{ \Carbon\Carbon::parse($breakdown['as_of'])->format('F j, Y') }}.
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">As Of</p>
                <p class="mt-2 text-lg font-bold text-gray-900 dark:text-white">
                    {{ \Carbon\Carbon::parse($breakdown['as_of'])->format('F j, Y') }}
                </p>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm dark:border-amber-800 dark:bg-amber-950/40">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Total Overdue Amount</p>
                <p class="mt-2 text-2xl font-extrabold text-amber-700 dark:text-amber-300">
                    ₱{{ number_format((float) $breakdown['total_overdue_amount'], 2) }}
                </p>
            </div>

            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm dark:border-rose-800 dark:bg-rose-950/40">
                <p class="text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">Total Penalty</p>
                <p class="mt-2 text-2xl font-extrabold text-rose-700 dark:text-rose-300">
                    ₱{{ number_format((float) $breakdown['total_penalty'], 2) }}
                </p>
            </div>

            <div class="rounded-2xl border border-emerald-600 bg-emerald-600 p-5 shadow-sm dark:border-emerald-500 dark:bg-emerald-500">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-50">Grand Total Due</p>
                <p class="mt-2 text-2xl font-black text-white">
                    ₱{{ number_format((float) $breakdown['grand_total_due'], 2) }}
                </p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Period</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Due Date</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Principal</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Interest</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Amortization</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Overdue Days</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Penalty Rate</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Penalty</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Installment Total Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($breakdown['rows'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {{ $row['period'] }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $row['due_date_formatted'] }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    ₱{{ number_format((float) $row['principal'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    ₱{{ number_format((float) $row['interest'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                    ₱{{ number_format((float) $row['amortization'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ $row['overdue_days'] }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ number_format((float) $row['effective_rate'], 2) }}%
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-rose-600 dark:text-rose-300">
                                    ₱{{ number_format((float) $row['penalty_amount'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-300">
                                    ₱{{ number_format((float) $row['total_due'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>