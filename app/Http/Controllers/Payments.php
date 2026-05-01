<?php

namespace App\Http\Controllers;

use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;
use App\Models\Profile;
use Exception;
use Illuminate\Http\Request;

class Payments extends Controller
{
    public function getPaymentStatus($id){
        try{
            $payments = CollectionAndPosting::where('loan_account_id', $id)->get();

            return response()->json([
                'payments' => $payments
            ]);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to get payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function PayLoan(Request $request){
        try{
            $pid = LoanAccount::where('looan_account_id', $request->loan_account_id)->value('profile_id');
            $profile = Profile::where('profile_id', $pid)->first();

            $membername = $profile->firstname .' '.$profile->lastname;

            CollectionAndPosting::create([
                'amount_paid' => $request->amount_paid,
                'loan_account_id' => $request->loan_account_id,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'reference_number' => $request->or_number,
                'posted_by_user_id' => $request->profileid,
                'status' => 'Posted',
                'notes' => $request->notes,
                'member_name' => $membername
            ]);

            return response()->json([
                'message' => 'payment was recorded successfully!'
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
