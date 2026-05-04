<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\LoanType;
use App\Models\MemberDetail;
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

    public function getLoanApplications()
    {
        try {
            $lola = LoanApplication::with('member.profile.user')
                ->where('status', 'Pending')
                ->get();

            return response()->json([
                'lola' => $lola,
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
            $loan = LoanAccount::with('profile')
                ->findOrFail($id);

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
            $loanAccounts = LoanAccount::where('profile_id', $pid)->where('status', 'Active')->get();

            return response()->json([
                'loanAccounts' => $loanAccounts,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get loan accounts',
            ]);
        }
    }
}
