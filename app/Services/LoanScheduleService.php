<?php

namespace App\Services;

use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;
use App\Models\PenaltyRule;
use Carbon\Carbon;

class LoanScheduleService
{
    public function __construct(
        protected LoanAmortizationService $amortizationService
    ) {}

    public function build(LoanAccount $loanAccount): array
    {
        if (
            (float) $loanAccount->principal_amount <= 0 ||
            (int) $loanAccount->term_months <= 0 ||
            $loanAccount->release_date === null
        ) {
            return [];
        }
        
        $baseRows = $this->amortizationService->generate(
            loanAmount: (float) $loanAccount->principal_amount,
            monthlyInterestRatePercent: (float) $loanAccount->interest_rate,
            termMonths: (int) $loanAccount->term_months,
            releaseDate: $loanAccount->release_date,
        );

        $schedule = collect($baseRows)->map(function (array $row) {
            return [
                'period' => $row['month'],
                'due_date' => $row['due_date'],

                'scheduled_principal' => (float) $row['principal'],
                'scheduled_interest' => (float) $row['interest'],
                'scheduled_amortization' => (float) $row['amortization'],
                'ending_balance' => (float) $row['balance'],

                'penalty' => 0,
                'paid_penalty' => 0,
                'paid_interest' => 0,
                'paid_principal' => 0,
                'total_paid' => 0,
                'unpaid_amount' => (float) $row['amortization'],
                'status' => 'Unpaid',
                'days_late' => 0,
            ];
        })->values()->all();

        $penaltyRule = $this->getActivePenaltyRule();

        $schedule = $this->applyPenaltyAndStatuses(
            schedule: $schedule,
            loanAccount: $loanAccount,
            penaltyRule: $penaltyRule,
        );

        $paymentsQuery = CollectionAndPosting::query()
                ->where('loan_account_id', $loanAccount->loan_account_id)
                ->where('status', 'Posted');

            if ($loanAccount->restructured_at) {
                $paymentsQuery->whereDate('payment_date', '>=', $loanAccount->restructured_at);
            }

            $payments = $paymentsQuery
                ->orderBy('payment_date')
                ->orderBy('id')
                ->get();

        foreach ($payments as $payment) {
            $schedule = $this->applyPayment(
                schedule: $schedule,
                amount: (float) $payment->amount_paid,
                paymentDate: Carbon::parse($payment->payment_date),
            );
        }

        $schedule = $this->refreshStatusesAfterPayments($schedule);

        return $schedule;
    }

    public function buildPaymentHistory(LoanAccount $loanAccount): array
    {
        if (
            (float) $loanAccount->principal_amount <= 0 ||
            (int) $loanAccount->term_months <= 0 ||
            $loanAccount->release_date === null
        ) {
            return [];
        }

        $baseRows = $this->amortizationService->generate(
            loanAmount: (float) $loanAccount->principal_amount,
            monthlyInterestRatePercent: (float) $loanAccount->interest_rate,
            termMonths: (int) $loanAccount->term_months,
            releaseDate: $loanAccount->release_date,
        );

        $schedule = collect($baseRows)->map(function (array $row) {
            return [
                'period' => $row['month'],
                'due_date' => $row['due_date'],

                'scheduled_principal' => (float) $row['principal'],
                'scheduled_interest' => (float) $row['interest'],
                'scheduled_amortization' => (float) $row['amortization'],
                'ending_balance' => (float) $row['balance'],

                'penalty' => 0,
                'paid_penalty' => 0,
                'paid_interest' => 0,
                'paid_principal' => 0,
                'total_paid' => 0,
                'unpaid_amount' => (float) $row['amortization'],
                'status' => 'Unpaid',
                'days_late' => 0,
            ];
        })->values()->all();

        $penaltyRule = $this->getActivePenaltyRule();

        $schedule = $this->applyPenaltyAndStatuses(
            schedule: $schedule,
            loanAccount: $loanAccount,
            penaltyRule: $penaltyRule,
        );

        $paymentsQuery = CollectionAndPosting::query()
            ->where('loan_account_id', $loanAccount->loan_account_id)
            ->where('status', 'Posted');

        if ($loanAccount->restructured_at) {
            $paymentsQuery->whereDate('payment_date', '>=', $loanAccount->restructured_at);
        }

        $payments = $paymentsQuery
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        $loanAmount = (float) $loanAccount->principal_amount;

        $history = [];

        foreach ($payments as $payment) {
            $beforePaidPenalty = array_sum(array_map(fn (array $row): float => (float) $row['paid_penalty'], $schedule));
            $beforePaidInterest = array_sum(array_map(fn (array $row): float => (float) $row['paid_interest'], $schedule));
            $beforePaidPrincipal = array_sum(array_map(fn (array $row): float => (float) $row['paid_principal'], $schedule));

            $schedule = $this->applyPayment(
                schedule: $schedule,
                amount: (float) $payment->amount_paid,
                paymentDate: Carbon::parse($payment->payment_date),
            );

            $afterPaidPenalty = array_sum(array_map(fn (array $row): float => (float) $row['paid_penalty'], $schedule));
            $afterPaidInterest = array_sum(array_map(fn (array $row): float => (float) $row['paid_interest'], $schedule));
            $afterPaidPrincipal = array_sum(array_map(fn (array $row): float => (float) $row['paid_principal'], $schedule));

            $paidPenalty = round(max($afterPaidPenalty - $beforePaidPenalty, 0), 2);
            $paidInterest = round(max($afterPaidInterest - $beforePaidInterest, 0), 2);
            $paidPrincipal = round(max($afterPaidPrincipal - $beforePaidPrincipal, 0), 2);

            $remainingBalance = round(max($loanAmount - $afterPaidPrincipal, 0), 2);

            $history[] = [
                'payment_date' => $payment->payment_date,
                'amount_paid' => (float) $payment->amount_paid,
                'penalty_paid' => $paidPenalty,
                'interest_paid' => $paidInterest,
                'principal_paid' => $paidPrincipal,
                'remaining_balance' => $remainingBalance,
                'reference_number' => $payment->reference_number,
                'payment_method' => $payment->payment_method,
                'notes' => $payment->notes,
            ];
        }

        return array_reverse($history);
    }

    protected function applyPayment(array $schedule, float $amount, Carbon $paymentDate): array
    {
        foreach ($schedule as &$row) {
            if ($amount <= 0) {
                break;
            }

            $remainingPenalty = max(0, round($row['penalty'] - $row['paid_penalty'], 2));
            if ($remainingPenalty > 0) {
                $pay = min($amount, $remainingPenalty);
                $row['paid_penalty'] += $pay;
                $row['total_paid'] += $pay;
                $amount -= $pay;
            }

            $remainingInterest = max(0, round($row['scheduled_interest'] - $row['paid_interest'], 2));
            if ($amount > 0 && $remainingInterest > 0) {
                $pay = min($amount, $remainingInterest);
                $row['paid_interest'] += $pay;
                $row['total_paid'] += $pay;
                $amount -= $pay;
            }

            $remainingPrincipal = max(0, round($row['scheduled_principal'] - $row['paid_principal'], 2));
            if ($amount > 0 && $remainingPrincipal > 0) {
                $pay = min($amount, $remainingPrincipal);
                $row['paid_principal'] += $pay;
                $row['total_paid'] += $pay;
                $amount -= $pay;
            }

            $row['unpaid_amount'] = round(
                max(($row['scheduled_amortization'] + $row['penalty']) - $row['total_paid'], 0),
                2
            );
        }

        unset($row);

        return $schedule;
    }

    protected function applyPenaltyAndStatuses(
        array $schedule,
        LoanAccount $loanAccount,
        ?PenaltyRule $penaltyRule
    ): array {
        $today = now();

        foreach ($schedule as &$row) {
            $dueDate = Carbon::parse($row['due_date']);
            $graceDays = (int) ($penaltyRule?->grace_period_days ?? 0);
            $effectiveDueDate = $dueDate->copy()->addDays($graceDays);

            if (! $today->gt($effectiveDueDate)) {
                continue;
            }

            if ($row['unpaid_amount'] <= 0) {
                continue;
            }

            $row['days_late'] = (int) floor($effectiveDueDate->floatDiffInDays($today));

            $row['penalty'] = $this->calculatePenalty(
                row: $row,
                loanAccount: $loanAccount,
                penaltyRule: $penaltyRule,
                daysLate: $row['days_late'],
            );

            $row['unpaid_amount'] = round(
                max(($row['scheduled_amortization'] + $row['penalty']) - $row['total_paid'], 0),
                2
            );

            if ($row['total_paid'] > 0) {
                $row['status'] = 'Partial / Late';
            } else {
                $row['status'] = 'Late';
            }
        }

        unset($row);

        return $schedule;
    }

    protected function refreshStatusesAfterPayments(array $schedule): array
    {
        foreach ($schedule as &$row) {
            $totalDue = round($row['scheduled_amortization'] + $row['penalty'], 2);
            $row['unpaid_amount'] = round(max($totalDue - $row['total_paid'], 0), 2);

            if ($row['unpaid_amount'] <= 0) {
                $row['status'] = 'Paid';
            } elseif ($row['total_paid'] > 0 && $row['days_late'] > 0) {
                $row['status'] = 'Partial / Late';
            } elseif ($row['total_paid'] > 0) {
                $row['status'] = 'Partial';
            } elseif ($row['days_late'] > 0) {
                $row['status'] = 'Late';
            } else {
                $row['status'] = 'Unpaid';
            }
        }

        unset($row);

        return $schedule;
    }

    protected function calculatePenalty(
        array $row,
        LoanAccount $loanAccount,
        ?PenaltyRule $penaltyRule,
        int $daysLate
    ): float {
        if (! $penaltyRule || $penaltyRule->status !== 'active') {
            return 0;
        }

        $rate = (float) $penaltyRule->rate;

        if ($rate <= 0) {
            return 0;
        }

        $basis = match ($penaltyRule->type) {
            'principal' => (float) $row['scheduled_principal'],
            'balance' => (float) $row['ending_balance'],
            default => (float) $row['scheduled_amortization'],
        };

        $multiplier = match ($penaltyRule->frequency) {
            'daily' => max($daysLate, 1),
            'monthly' => max((int) ceil($daysLate / 30), 1),
            'one_time' => 1,
            default => 1,
        };

        return round($basis * ($rate / 100) * $multiplier, 2);
    }

    protected function getActivePenaltyRule(): ?PenaltyRule
    {
        return PenaltyRule::query()
            ->where('status', 'active')    
            ->latest('id')
            ->first();
    }
}
