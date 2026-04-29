<?php

namespace App\Http\Controllers;

use App\Models\LoanApplication as ModelsLoanApplication;
use App\Models\LoanApplicationCashflow;
use App\Models\LoanApplicationDocument;
use App\Models\LoanType;
use App\Models\MemberDetail;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\Request;

class LoanApplication extends Controller
{
    public function applyLoan(Request $request)
    {
        try {
            $lid = LoanType::where('name', $request->loanType)->value('loan_type_id');
            $mid = MemberDetail::where('profile_id', $request->profile_id)->value('id');
            $uid = User::where('profile_id', $request->profile_id)->value('user_id');

            $documentPath = null;

            $addloanapp = ModelsLoanApplication::create([
                'member_id' => $mid,
                'loan_type_id' => $lid,
                'application_type' => 'New',
                'amount_requested' => $request->amountRequested,
                'term_months' => $request->termMonths,
                'status' => 'Pending',
            ]);

            if (! $addloanapp) {
                throw new Exception('Unable to add loan application to database!');
            }

            $laid = $addloanapp->loan_application_id;

            if ($request->hasFile('collateral_document')) {

                $documentPath = $request->file('collateral_document')
                    ->store('loan-collaterals', 'public');

                LoanApplicationDocument::create([
                    'loan_application_id' => $laid,
                    'document_type' => $request->collateral_type,
                    'file_path' => $documentPath,
                    'uploaded_by_user_id' => $uid,
                ]);
            }

            if ($request->hasFile('cashflow_document')) {
                $cashflowDocumentPath = $request->file('cashflow_document')
                    ->store('cashflow-documents', 'public');

                $addloanapp->update([
                    'cashflow_documents' => $cashflowDocumentPath ? json_encode([$cashflowDocumentPath]) : null,
                ]);
            }

            $salary = (float) $request->input('income_salary', 0);
            $businessIncome = (float) $request->input('income_businessIncome', 0);
            $remittances = (float) $request->input('income_remittances', 0);
            $otherIncome = (float) $request->input('income_otherIncome', 0);

            if ($salary > 0) {
                LoanApplicationCashflow::create([
                    'loan_application_id' => $addloanapp->loan_application_id,
                    'row_type' => 'income',
                    'category' => 'salary',
                    'label' => 'Salary / Wages',
                    'amount' => $salary,
                    'notes' => 'Regular monthly salary',
                ]);
            }

            if ($businessIncome > 0) {
                LoanApplicationCashflow::create([
                    'loan_application_id' => $addloanapp->loan_application_id,
                    'row_type' => 'income',
                    'category' => 'business_income',
                    'label' => 'Business income',
                    'amount' => $businessIncome,
                    'notes' => 'Busines income',
                ]);
            }

            if ($remittances > 0) {
                LoanApplicationCashflow::create([
                    'loan_application_id' => $addloanapp->loan_application_id,
                    'row_type' => 'income',
                    'category' => 'remittances',
                    'label' => 'Remittances',
                    'amount' => $remittances,
                    'notes' => 'Remittances',
                ]);
            }

            if ($otherIncome > 0) {
                LoanApplicationCashflow::create([
                    'loan_application_id' => $addloanapp->loan_application_id,
                    'row_type' => 'income',
                    'category' => 'other_income',
                    'label' => 'Other income',
                    'amount' => $otherIncome,
                    'notes' => 'Other income',
                ]);
            }

            $livingExpenses = (float) $request->input('expenses_livingExpenses', 0);
            $businessExpenses = (float) $request->input('expenses_businessExpenses', 0);
            $loanPayments = (float) $request->input('expenses_loanPayments', 0);
            $otherExpenses = (float) $request->input('expenses_otherExpenses', 0);

            if ($livingExpenses > 0) {
                LoanApplicationCashflow::create([
                    'loan_application_id' => $addloanapp->loan_application_id,
                    'row_type' => 'expense',
                    'category' => 'living_expenses',
                    'label' => 'Living expenses',
                    'amount' => $livingExpenses,
                    'notes' => 'Living expenses',
                ]);
            }

            if ($businessExpenses > 0) {
                LoanApplicationCashflow::create([
                    'loan_application_id' => $addloanapp->loan_application_id,
                    'row_type' => 'expense',
                    'category' => 'business_expenses',
                    'label' => 'Business expenses',
                    'amount' => $businessExpenses,
                    'notes' => 'Business expenses',
                ]);
            }

            if ($loanPayments > 0) {
                LoanApplicationCashflow::create([
                    'loan_application_id' => $addloanapp->loan_application_id,
                    'row_type' => 'expense',
                    'category' => 'existing_loan_payments',
                    'label' => 'Existing loan payments',
                    'amount' => $loanPayments,
                    'notes' => 'existing loan payments',
                ]);
            }

            if ($otherExpenses > 0) {
                LoanApplicationCashflow::create([
                    'loan_application_id' => $addloanapp->loan_application_id,
                    'row_type' => 'expense',
                    'category' => 'other_expenses',
                    'label' => 'Other expenses',
                    'amount' => $otherExpenses,
                    'notes' => 'Other expenses',
                ]);
            }

            Notification::create([
                'user_id' => $uid,
                'title' => 'Loan Application',
                'description' => 'Your request for loan application was sent for approval',
            ]);

            $loanTitle = 'Loan application submitted';
            $loanDesc = "Your loan application (#{$laid}) of PHP {$request->amountRequested} is now pending review.";

            app(NotificationService::class)->notifyProfile(
                $request->profile_id,
                $loanTitle,
                $loanDesc
            );

            app(NotificationService::class)->notifyAdmins(
                'New loan application submitted',
                "Loan application #{$laid} has been submitted by profile_id {$request->profile_id}."
            );

            return response()->json([
                'message' => 'Loan application was successfully sent!',
                'loan_application_id' => $laid,
                'file_path' => $documentPath,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to send loan application',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function viewMemberLoanApplications(Request $request)
    {
        try {
            $mid = MemberDetail::where('profile_id', $request->memberId)->value('id');

            if (! $mid) {
                throw new Exception('Member not found');
            }

            $loanApplications = ModelsLoanApplication::where('member_id', $mid)
                ->with('type')
                ->latest()
                ->get();

            if ($loanApplications->isEmpty()) {
                throw new Exception('There are no loan applications for this member!');
            }

            return response()->json([
                'loanApplications' => $loanApplications,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to fetch loan applications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancelLoanApplication(Request $request)
    {
        try {
            $mid = ModelsLoanApplication::where('loan_application_id', $request->loanApplicationId)->value('member_id');
            $pid = MemberDetail::where('id', $mid)->value('profile_id');
            $uid = User::where('profile_id', $pid)->value('user_id');

            $update = ModelsLoanApplication::where('loan_application_id', $request->loanApplicationId)
                ->update(['status' => 'Cancelled']);

            if (! $update) {
                throw new Exception('Unable to update loan application!');
            }

            Notification::create([
                'user_id' => $uid,
                'title' => 'Loan Application',
                'description' => 'Loan Application was successfully cancelled!',
            ]);

            return response()->json([
                'status' => 'Success',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to cancel loan application',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
