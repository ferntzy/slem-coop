<?php

namespace App\Http\Controllers;

use App\Models\CollectionAndPosting;
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
}
