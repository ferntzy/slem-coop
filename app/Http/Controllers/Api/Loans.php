<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\LoanType;
use App\Models\MemberDetail;
use App\Models\Profile;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class Loans extends Controller
{
    public function getApprovedLoans()
    {
        try {
            $noal = LoanApplication::where('status', 'Approved')->count();

            return response()->json([
                'noal' => $noal,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get approved loans',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getPendingLoans()
    {
        try {
            $pendingLoans = LoanApplication::where('status', 'Pending')->count();

            if (! $pendingLoans) {
                throw new Exception('There is no pending loan application');
            }

            return response()->json([
                'pendingLoans' => $pendingLoans,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get Pending loans',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getLoanApplications($id)
    {
        try {
           $bid = MemberDetail::whereHas('profile', function ($q) use ($id) {
                $q->where('profile_id', $id);
            })->value('branch_id');

            $lola = LoanApplication::with(['member.profile', 'member.branch'])
                ->where('status', 'Pending')
                ->whereHas('member', function ($query) use ($bid) {
                    $query->where('branch_id', $bid);
                })
                ->get();

            return response()->json([
                'lola' => $lola,
                'bid' => $bid
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get loan applications',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getLoanApplication(Request $request)
    {
        try {
            $detail = LoanApplication::with('member.profile.user')
                ->where('loan_application_id', $request->id)
                ->first();

            if ($detail && $detail->collateral_document) {
                $detail->collateral_document_url = asset('storage/'.$detail->collateral_document);
            }

            return response()->json([
                'detail' => $detail,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get loan application detail',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function declineLoanApplication(Request $request)
    {
        try {
            LoanApplication::where('loan_application_id', $request->id)->update(['status' => 'Rejected']);

            $memberId = LoanApplication::where('loan_application_id', $request->id)->value('member_id');
            $profileId = $memberId ? MemberDetail::where('id', $memberId)->value('profile_id') : null;

            if ($profileId) {
                app(NotificationService::class)->notifyProfileWithPush(
                    $profileId,
                    'Loan application rejected',
                    "Your loan application #{$request->id} has been rejected.",
                    notifiableType: 'loan_application',
                    notifiableId: (int) $request->id
                );
            }

            $actor = Auth::guard('sanctum')->user() ?? auth()->user();
            if ($actor) {
                app(NotificationService::class)->notifyUserWithPush(
                    $actor->user_id,
                    'Loan application rejected',
                    "You rejected loan application #{$request->id}.",
                    notifiableType: 'loan_application',
                    notifiableId: (int) $request->id
                );
            }

            return response()->json([
                'success' => 'Loan application was successfully declined!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to decline loan application',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function approveLoanApplication(Request $request)
    {
        try {
            $loanapp = LoanApplication::where('loan_application_id', $request->id)->firstOrFail();
            $loanapp->update(['status' => 'Approved']);
            $mid = $loanapp->member_id;
            $pid = MemberDetail::where('id', $mid)->value('profile_id');
            $amount = $loanapp->amount_requested;
            $intRate = LoanType::where('loan_type_id', $loanapp->loan_type_id)->value('max_interest_rate');
            $termMonths = (int) $loanapp->term_months;
            $totalInterest = $amount * ($intRate / 100) * $termMonths;
            $monthlyAmortization = ($amount + $totalInterest) / $termMonths;
            $maturityDate = now()->addMonths($termMonths)->toDateString();

            LoanAccount::create([
                'profile_id' => $pid,
                'loan_application_id' => $request->id,
                'penalty_rule_id' => $loanapp->penalty_rule_id,
                'principal_amount' => $amount,
                'shared_capital_fee' => '0.00',
                'insurance_fee' => '0.00',
                'processing_fee' => '0.00',
                'coop_fee_total' => '0.00',
                'net_release_amount' => '0.00',
                'interest_rate' => $intRate,
                'term_months' => $termMonths,
                'release_date' => Carbon::now()->toDateString(),
                'maturity_date' => $maturityDate,
                'monthly_amortization' => $monthlyAmortization,
                'balance' => $amount,
                'status' => 'Active',
            ]);

            $fcmToken = User::where('profile_id', $pid)->value('fcm_token');

            if ($fcmToken) {
                Http::withHeaders(['Content-Type' => 'application/json'])
                    ->post('https://exp.host/--/push/v2/send', [
                        'to' => $fcmToken,
                        'title' => 'Loan Approved',
                        'body' => 'Your loan application has been approved!',
                        'sound' => 'default',
                        'data' => [
                            'type' => 'loan_approved',
                            'loan_application_id' => $request->id,
                        ],
                    ]);
            }

            if ($pid) {
                if ($fcmToken) {
                    app(NotificationService::class)->notifyProfile(
                        $pid,
                        'Loan application approved',
                        'Your loan application has been approved!',
                        notifiableType: 'loan_application',
                        notifiableId: (int) $request->id
                    );
                } else {
                    app(NotificationService::class)->notifyProfileWithPush(
                        $pid,
                        'Loan application approved',
                        'Your loan application has been approved!',
                        notifiableType: 'loan_application',
                        notifiableId: (int) $request->id
                    );
                }
            }

            $actor = Auth::guard('sanctum')->user() ?? auth()->user();
            if ($actor) {
                app(NotificationService::class)->notifyUserWithPush(
                    $actor->user_id,
                    'Loan application approved',
                    "You approved loan application #{$request->id}.",
                    notifiableType: 'loan_application',
                    notifiableId: (int) $request->id
                );
            }

            return response()->json([
                'success' => 'Loan application was successfully approved!',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to approve loan application',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getLoans()
    {
        try {
            $loans = LoanAccount::with('profile')->get();

            return response()->json([
                'loans' => $loans,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get loans',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getLoanDetail($id)
    {
        try {
            $loan = LoanAccount::with(['profile', 'collectionsAndPostings' => function ($q) {
                $q->where('status', 'posted')
                ->orderBy('payment_date', 'desc');
            }])->findOrFail($id);

            return response()->json([
                'loan' => $loan,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get loan detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getLoanAccountsById($id)
    {
        try {
            $pid = MemberDetail::where('id', $id)->value('profile_id');

            $loanAccounts = LoanAccount::where('profile_id', $pid)
                ->where('status', 'Active')
                ->withSum(
                    ['collectionsAndPostings as total_paid' => function ($query) {
                        $query->where('status', 'posted'); // adjust to your posted/approved status value
                    }],
                    'amount_paid'
                )
                ->get();

            return response()->json([
                'loanAccounts' => $loanAccounts,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get loan accounts',
            ]);
        }
    }

    public function getPendingPaymentCount(){
        try{
            $today = now()->toDateString();

            // Get all active loan accounts
            $loanAccounts = LoanAccount::where('status', 'Active')->get();

            $pendingCount = 0;
            $overdueCount = 0;

            foreach ($loanAccounts as $loan) {
                $principal     = (float) $loan->principal_amount;
                $annualRate    = (float) $loan->interest_rate;
                $monthlyRate   = $annualRate / 100 / 12;
                $termMonths    = (int) $loan->term_months;
                $monthlyPayment = (float) $loan->monthly_amortization;
                $releaseDate   = $loan->release_date;

                // Sum posted payments, sorted chronologically
                $payments = $loan->collectionsAndPostings()
                    ->where('status', 'posted')
                    ->orderBy('payment_date')
                    ->get(['amount_paid', 'payment_date']);

                $paymentPool  = 0.0;
                $paymentIndex = 0;
                $paymentCount = $payments->count();

                for ($i = 1; $i <= $termMonths; $i++) {
                    $dueDate = (clone $releaseDate)->addMonths($i);

                    // Absorb payments on or before this due date
                    while (
                        $paymentIndex < $paymentCount &&
                        $payments[$paymentIndex]->payment_date <= $dueDate->toDateString()
                    ) {
                        $paymentPool += (float) $payments[$paymentIndex]->amount_paid;
                        $paymentIndex++;
                    }

                    if ($paymentPool >= $monthlyPayment) {
                        // Period is paid
                        $paymentPool -= $monthlyPayment;
                    } elseif ($dueDate->toDateString() < $today) {
                        $overdueCount++;
                    } else {
                        $daysAhead = now()->diffInDays($dueDate, false);
                        if ($daysAhead <= 30) {
                            $pendingCount++;
                        }
                    }
                }
            }

            return response()->json([
                'pending_count' => $pendingCount,
                'overdue_count' => $overdueCount,
                'total'         => $pendingCount + $overdueCount,
            ]);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to get pending payment counts',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getPendingPayments(){
        try {
            $today = now()->toDateString();
            $results = [];

            $loanAccounts = LoanAccount::where('status', 'Active')
                ->with(['profile', 'collectionsAndPostings' => function ($q) {
                    $q->where('status', 'posted')->orderBy('payment_date');
                }])
                ->get();

            foreach ($loanAccounts as $loan) {
                $principal      = (float) $loan->principal_amount;
                $monthlyRate    = (float) $loan->interest_rate / 100 / 12;
                $termMonths     = (int) $loan->term_months;
                $monthlyPayment = (float) $loan->monthly_amortization;
                $releaseDate    = $loan->release_date;

                $payments     = $loan->collectionsAndPostings;
                $paymentPool  = 0.0;
                $paymentIndex = 0;
                $paymentCount = $payments->count();
                $balance      = $principal;

                for ($i = 1; $i <= $termMonths; $i++) {
                    $dueDate = (clone $releaseDate)->addMonths($i);

                    while (
                        $paymentIndex < $paymentCount &&
                        $payments[$paymentIndex]->payment_date <= $dueDate->toDateString()
                    ) {
                        $paymentPool += (float) $payments[$paymentIndex]->amount_paid;
                        $paymentIndex++;
                    }

                    $interest       = round($balance * $monthlyRate, 2);
                    $principalPay   = round($monthlyPayment - $interest, 2);
                    $endingBalance  = max(round($balance - $principalPay, 2), 0);

                    if ($paymentPool >= $monthlyPayment) {
                        $paymentPool -= $monthlyPayment;
                    } else {
                        $dueDateStr = $dueDate->toDateString();
                        $daysAhead  = now()->diffInDays($dueDate, false);

                        if ($dueDateStr >= $today && $daysAhead <= 30) {
                            $results[] = [
                                'loan_account_id'   => $loan->loan_account_id,
                                'period'            => $i,
                                'due_date'          => $dueDateStr,
                                'days_until_due'    => (int) $daysAhead,
                                'monthly_payment'   => $monthlyPayment,
                                'interest_payment'  => $interest,
                                'principal_payment' => $principalPay,
                                'ending_balance'    => $endingBalance,
                                'member_name'       => $loan->profile?->full_name ?? 'Unknown',
                                'profile_id'        => $loan->profile_id,
                            ];
                        }
                    }

                    $balance = $endingBalance;
                }
            }

            // Sort by due date ascending
            usort($results, fn($a, $b) => strcmp($a['due_date'], $b['due_date']));

            return response()->json(['pending_payments' => $results]);

        } catch (Exception $e) {
            return response()->json(['message' => 'Unable to get pending payments']);
        }
    }

    public function getOverduePayments()
    {
        try {
            $today = now()->toDateString();
            $results = [];

            $loanAccounts = LoanAccount::where('status', 'Active')
                ->with(['profile', 'collectionsAndPostings' => function ($q) {
                    $q->where('status', 'posted')->orderBy('payment_date');
                }])
                ->get();

            foreach ($loanAccounts as $loan) {
                $principal      = (float) $loan->principal_amount;
                $monthlyRate    = (float) $loan->interest_rate / 100 / 12;
                $termMonths     = (int) $loan->term_months;
                $monthlyPayment = (float) $loan->monthly_amortization;
                $releaseDate    = $loan->release_date;

                $payments     = $loan->collectionsAndPostings;
                $paymentPool  = 0.0;
                $paymentIndex = 0;
                $paymentCount = $payments->count();
                $balance      = $principal;

                for ($i = 1; $i <= $termMonths; $i++) {
                    $dueDate = (clone $releaseDate)->addMonths($i);

                    while (
                        $paymentIndex < $paymentCount &&
                        $payments[$paymentIndex]->payment_date <= $dueDate->toDateString()
                    ) {
                        $paymentPool += (float) $payments[$paymentIndex]->amount_paid;
                        $paymentIndex++;
                    }

                    $interest      = round($balance * $monthlyRate, 2);
                    $principalPay  = round($monthlyPayment - $interest, 2);
                    $endingBalance = max(round($balance - $principalPay, 2), 0);

                    if ($paymentPool >= $monthlyPayment) {
                        $paymentPool -= $monthlyPayment;
                    } elseif ($dueDate->toDateString() < $today) {
                        // This period is overdue
                        $daysOverdue = now()->diffInDays($dueDate);
                        $results[] = [
                            'loan_account_id'   => $loan->loan_account_id,
                            'period'            => $i,
                            'due_date'          => $dueDate->toDateString(),
                            'days_overdue'      => (int) $daysOverdue,
                            'monthly_payment'   => $monthlyPayment,
                            'interest_payment'  => $interest,
                            'principal_payment' => $principalPay,
                            'ending_balance'    => $endingBalance,
                            'member_name'       => $loan->profile?->full_name ?? 'Unknown',
                            'profile_id'        => $loan->profile_id,
                        ];
                    }

                    $balance = $endingBalance;
                }
            }

            // Sort by most overdue first
            usort($results, fn($a, $b) => $b['days_overdue'] <=> $a['days_overdue']);

            return response()->json(['overdue_payments' => $results]);

        } catch (Exception $e) {
            return response()->json(['message' => 'Unable to get overdue payments']);
        }
    }
}
