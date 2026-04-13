@php
    $loanAccount = $record->loanAccount;

    $schedule = [];
    $penaltyRowsByPeriod = [];

    if ($loanAccount) {
        $schedule = app(\App\Services\LoanAmortizationService::class)->generate(
            loanAmount: (float) $loanAccount->principal_amount,
            monthlyInterestRatePercent: (float) $loanAccount->interest_rate,
            termMonths: (int) $loanAccount->term_months,
            releaseDate: $loanAccount->release_date,
        );

        $penaltyBreakdown = app(\App\Services\LoanPenaltyService::class)
            ->calculateForLoanAccount($loanAccount);

        $penaltyRowsByPeriod = collect($penaltyBreakdown['rows'] ?? [])
            ->keyBy('period')
            ->toArray();
    }

    $rows = collect($schedule)->map(function (array $row) use ($penaltyRowsByPeriod) {
        $penaltyRow = $penaltyRowsByPeriod[$row['period']] ?? [];

        $paid = (float) ($penaltyRow['paid_amount'] ?? 0);
        $scheduledAmount = (float) ($row['amortization'] ?? 0);
        $penaltyAmount = (float) ($penaltyRow['penalty_amount'] ?? 0);
        $unpaidAmount = (float) ($penaltyRow['unpaid_amount'] ?? max(0, $scheduledAmount - $paid));

        // What should be collected for this row when selected
        $selectableAmount = round($unpaidAmount + $penaltyAmount, 2);

        $status = $penaltyRow['status'] ?? (
            $paid >= $scheduledAmount ? 'Paid' : ($paid > 0 ? 'Partial / Late' : 'Unpaid')
        );

        return [
            'period' => (int) $row['period'],
            'due_date' => $row['due_date_formatted'] ?? $row['due_date'],
            'interest' => (float) ($row['interest'] ?? 0),
            'principal' => (float) ($row['principal'] ?? 0),
            'amortization' => $scheduledAmount,
            'penalty_amount' => $penaltyAmount,
            'paid_amount' => $paid,
            'unpaid_amount' => $unpaidAmount,
            'selectable_amount' => $selectableAmount,
            'status' => $status,
            'is_paid' => $status === 'Paid' || $unpaidAmount <= 0,
        ];
    })->values()->all();
@endphp

<div
    x-data="{
        rows: @js($rows),
        selectedPeriods: [],

        isRowLocked(rowIndex) {
            const row = this.rows[rowIndex];

            if (row.is_paid) return true;

            for (let i = 0; i < rowIndex; i++) {
                const previous = this.rows[i];

                if (!previous.is_paid && !this.selectedPeriods.includes(previous.period)) {
                    return true;
                }
            }

            return false;
        },

        toggleRow(period, checked) {
            const rowIndex = this.rows.findIndex(row => row.period === period);
            if (rowIndex === -1) return;

            const row = this.rows[rowIndex];

            if (row.is_paid) return;

            if (checked) {
                if (this.isRowLocked(rowIndex)) {
                    return;
                }

                if (!this.selectedPeriods.includes(period)) {
                    this.selectedPeriods.push(period);
                }
            } else {
                // unchecking one row also unchecks all rows after it
                this.selectedPeriods = this.selectedPeriods.filter(selectedPeriod => selectedPeriod < period);
            }

            this.syncAmount();
        },

        syncAmount() {
            const total = this.rows
                .filter(row => this.selectedPeriods.includes(row.period))
                .reduce((sum, row) => sum + Number(row.selectable_amount || 0), 0);

            const amountInput =
                document.querySelector('input[name=\"data[amount]\"]') ||
                document.querySelector('input[name=\"amount\"]');

            if (amountInput) {
                amountInput.value = total.toFixed(2);
                amountInput.dispatchEvent(new Event('input', { bubbles: true }));
                amountInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }"
    class="space-y-4"
>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th></th>
                <th>#</th>
                <th>Due Date</th>
                <th>Amortization</th>
                <th>Penalty</th>
                <th>Paid</th>
                <th>Unpaid</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, index) in rows" :key="row.period">
                <tr>
                    <td style="padding: 8px; text-align: center;">
                        <template x-if="!row.is_paid">
                            <input
                                type="checkbox"
                                :checked="selectedPeriods.includes(row.period)"
                                :disabled="isRowLocked(index)"
                                @change="toggleRow(row.period, $event.target.checked)"
                            >
                        </template>
                    </td>

                    <td x-text="row.period"></td>
                    <td x-text="row.due_date"></td>
                    <td x-text="`₱${Number(row.amortization).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></td>
                    <td x-text="`₱${Number(row.penalty_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></td>
                    <td x-text="`₱${Number(row.paid_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></td>
                    <td x-text="`₱${Number(row.unpaid_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></td>
                    <td x-text="row.status"></td>
                </tr>
            </template>
        </tbody>
    </table>

    <div style="font-weight: 600;">
        Total Selected:
        <span
            x-text="`₱${rows
                .filter(row => selectedPeriods.includes(row.period))
                .reduce((sum, row) => sum + Number(row.selectable_amount || 0), 0)
                .toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"
        ></span>
    </div>
</div>