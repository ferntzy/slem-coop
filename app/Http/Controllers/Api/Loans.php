<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use Exception;
use Illuminate\Http\Request;

class Loans extends Controller
{
    public function getApprovedLoans(){
        try{
            $aLoans = LoanApplication::where('status', 'Approved')->count();

            if(!$aLoans){
                throw new Exception('There are no approved loans');
            }

            return response()->json([
                'approvedLoans' => $aLoans
            ]);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to get approved loans',
                'error' => $e->getMessage()
            ]);
        }
    }   
}
