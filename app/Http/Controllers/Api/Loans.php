<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\LoanType;
use App\Models\MemberDetail;
use Exception;
use Illuminate\Http\Request;

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

    public function getLoanApplication(Request $request){
        try{
            $detail = LoanApplication::with('member.profile.user')
                ->where('loan_application_id', $request->id)
                ->first();

            if ($detail && $detail->collateral_document) {
                $detail->collateral_document_url = asset('storage/' . $detail->collateral_document);
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

    public function declineLoanApplication(Request $request){
        try{
            LoanApplication::where('loan_application_id', $request->id)->update(['status'=>'Rejected']);

            return response()->json([
                'success' => 'Loan application was successfully declined!'
            ], 200);
        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to decline loan application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approveLoanApplication(Request $request){
        try{
            $loanapp = LoanApplication::where('loan_application_id', $request->id)->firstOrFail();
            $loanapp->update(['status' => 'Approved']);
            $mid = $loanapp->member_id;
            $pid = MemberDetail::where('member_id', $mid)->value('profile_id');
            $amount = $loanapp->amount_requested;
            $intRate = LoanType::where('loan_type_id', $loanapp->loan_type_id)->value('max_interest_rate');
            $termMonths = $loanapp->term_months;
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
                'release_date' => $loanapp->release_date,
                'maturity_date' => $maturityDate,
                'monthly_amortization' => $monthlyAmortization,
                'balance' => $amount,
                'status' => 'Active',
            ]);

            return response()->json([
                'success' => 'Loan application was successfully approved!',
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to approve loan application',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
