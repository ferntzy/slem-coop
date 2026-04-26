<?php

namespace App\Http\Controllers;

use App\Models\SavingsAccount as ModelsSavingsAccount;
use Exception;
use Illuminate\Http\Request;

class SavingsAccount extends Controller
{
    public function getSavingsAccount(Request $request){
        try{
            $savings = ModelsSavingsAccount::where('profile_id', $request->id)->first();

            return response()->json([
                'savings' => $savings
            ]);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to get savings data',
                'error' => $e->getMessage()
            ]);
        }
    }
}
