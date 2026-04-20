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
    <div class="py-3 text-sm text-gray-500 dark:text-zinc-300">
        No amortization schedule available.
    </div>
@else
    <div class="overflow-x-auto rounded-2xl border border-emerald-100 bg-white dark:border-zinc-800 dark:bg-black">
        <table class="w-full border-collapse text-sm text-gray-700 dark:text-zinc-100">
            <thead>
                <tr class="bg-emerald-50 dark:bg-black">
                    <th class="border-b border-emerald-100 px-3.5 py-3 text-left font-bold dark:border-zinc-700 dark:text-zinc-100">#</th>
                    <th class="border-b border-emerald-100 px-3.5 py-3 text-left font-bold dark:border-zinc-700 dark:text-zinc-100">Due Date</th>
                    <th class="border-b border-emerald-100 px-3.5 py-3 text-right font-bold dark:border-zinc-700 dark:text-zinc-100">Interest</th>
                    <th class="border-b border-emerald-100 px-3.5 py-3 text-right font-bold dark:border-zinc-700 dark:text-zinc-100">Principal</th>
                    <th class="border-b border-emerald-100 px-3.5 py-3 text-right font-bold dark:border-zinc-700 dark:text-zinc-100">Amortization</th>
                    <th class="border-b border-emerald-100 px-3.5 py-3 text-right font-bold dark:border-zinc-700 dark:text-zinc-100">Penalty</th>
                    <th class="border-b border-emerald-100 px-3.5 py-3 text-right font-bold dark:border-zinc-700 dark:text-zinc-100">Paid</th>
                    <th class="border-b border-emerald-100 px-3.5 py-3 text-right font-bold dark:border-zinc-700 dark:text-zinc-100">Unpaid</th>
                    <th class="border-b border-emerald-100 px-3.5 py-3 text-left font-bold dark:border-zinc-700 dark:text-zinc-100">Status</th>
                    <th class="border-b border-emerald-100 px-3.5 py-3 text-right font-bold dark:border-zinc-700 dark:text-zinc-100">Balance</th>
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

                        $badgeClasses = match ($status) {
                            'Paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300',
                            'Partial' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300',
                            default => 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300',
                        };

                        $rowClasses = $loop->odd ? 'bg-white dark:bg-black' : 'bg-emerald-50/30 dark:bg-zinc-950';
                    @endphp

                    <tr class="{{ $rowClasses }}">
                        <td class="whitespace-nowrap border-b border-slate-200 px-3.5 py-3 dark:border-zinc-800">
                            {{ $row['month'] ?? $loop->iteration }}
                        </td>

                        <td class="whitespace-nowrap border-b border-slate-200 px-3.5 py-3 dark:border-zinc-800">
                            {{ $row['due_date'] }}
                        </td>

                        <td class="whitespace-nowrap border-b border-slate-200 px-3.5 py-3 text-right dark:border-zinc-800">
                            ₱{{ number_format((float) $row['interest'], 2) }}
                        </td>

                        <td class="whitespace-nowrap border-b border-slate-200 px-3.5 py-3 text-right dark:border-zinc-800">
                            ₱{{ number_format((float) $row['principal'], 2) }}
                        </td>

                        <td class="whitespace-nowrap border-b border-slate-200 px-3.5 py-3 text-right font-semibold text-gray-900 dark:border-zinc-800 dark:text-zinc-100">
                            ₱{{ number_format((float) $row['amortization'], 2) }}
                        </td>

                        <td class="whitespace-nowrap border-b border-slate-200 px-3.5 py-3 text-right font-bold text-rose-600 dark:border-zinc-800 dark:text-rose-300">
                            ₱{{ number_format((float) $penalty, 2) }}
                        </td>

                        <td class="whitespace-nowrap border-b border-slate-200 px-3.5 py-3 text-right font-semibold text-emerald-600 dark:border-zinc-800 dark:text-emerald-300">
                            ₱{{ number_format((float) $paid, 2) }}
                        </td>

                        <td class="whitespace-nowrap border-b border-slate-200 px-3.5 py-3 text-right font-semibold text-amber-600 dark:border-zinc-800 dark:text-amber-300">
                            ₱{{ number_format((float) $unpaid, 2) }}
                        </td>

                        <td class="whitespace-nowrap border-b border-slate-200 px-3.5 py-3 dark:border-zinc-800">
                            <span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClasses }}">
                                {{ $status }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap border-b border-slate-200 px-3.5 py-3 text-right dark:border-zinc-800">
                            ₱{{ number_format((float) $row['balance'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif