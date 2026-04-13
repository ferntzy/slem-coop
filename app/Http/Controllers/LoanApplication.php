<?php

namespace App\Http\Controllers;

use App\Models\LoanApplication as ModelsLoanApplication;
use App\Models\LoanApplicationDocument;
use App\Models\LoanType;
use App\Models\MemberDetail;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\Request;
use App\Services\SmsService;

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
                'status' => 'Pending'
            ]);

            if (!$addloanapp) {
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
                    'uploaded_by_user_id' => $uid
                ]);
            }

            // Send SMS to user
            // $userPhoneNumber = $request->phone_number; // or get from database
            // $this->smsService->sendBulkSms(
            //     numbers: [$userPhoneNumber],
            //     message: 'Your loan application has been received. We will review it shortly.',
            //     senderId: 'CoopLoan'
            // );

            Notification::create([
                'user_id' => $uid,
                'title' => 'Loan Application',
                'description' => 'Your request for loan application was sent for approval'
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
                'file_path' => $documentPath
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to send loan application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function viewMemberLoanApplications(Request $request)
    {
        try {
            $mid = MemberDetail::where('profile_id', $request->memberId)->value('id');

            if (!$mid) {
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
                'loanApplications' => $loanApplications
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to fetch loan applications',
                'error' => $e->getMessage()
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

            if (!$update) {
                throw new Exception('Unable to update loan application!');
            }

            Notification::create([
                'user_id' => $uid,
                'title' => 'Loan Application',
                'description' => 'Loan Application was successfully cancelled!'
            ]);

            return response()->json([
                'status' => 'Success'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to cancel loan application',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
