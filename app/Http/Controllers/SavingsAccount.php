<?php

namespace App\Http\Controllers;

use App\Models\SavingsAccount as ModelsSavingsAccount;
use App\Models\SavingsAccountTransaction;
use Exception;
use Illuminate\Http\Request;

class SavingsAccount extends Controller
{
    public function getSavingsAccount($id){
        try{
            $totalDeposit = SavingsAccountTransaction::where('profile_id', $id)->where('type', 'Deposit')->sum('deposit');
            $totalWithdraw = SavingsAccountTransaction::where('profile_id', $id)->where('type', 'Withdraw')->sum('withdrawal');
            $totalAmount = SavingsAccountTransaction::where('profile_id', $id)->sum('amount');

            $currentBalance = $totalAmount - ($totalDeposit + $totalWithdraw);

            return response()->json([
                'data' => [
                    'total_deposit'   => $totalDeposit,
                    'total_withdraw'  => $totalWithdraw,
                    'total_amount'    => $totalAmount,
                    'current_balance' => $currentBalance,
                ]
            ]);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to get savings data',
                'error' => $e->getMessage()
            ]);
        }
    }
}
