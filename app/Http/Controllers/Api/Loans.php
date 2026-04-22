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
            $noal = LoanApplication::where('status', 'Approved')->count();

            return response()->json([
                'noal' => $noal
            ]);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to get approved loans',
                'error' => $e->getMessage()
            ]);
        }
    }   

    public function getPendingLoans(){
        try{
            $pendingLoans = LoanApplication::where('status', 'Pending')->count();

            if(!$pendingLoans){
                throw new Exception('There is no pending loan application');
            }

            return response()->json([
                'pendingLoans' => $pendingLoans
            ]);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to get Pending loans',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getLoanApplications(){
        try {
            $lola = LoanApplication::with('member.profile.user')
                ->where('status', 'Pending')
                ->get();

            return response()->json([
                'lola' => $lola
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get loan applications',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getLoanApplication(Request $request){
        try{
            $detail = LoanApplication::with('member.profile.user')->where('loan_application_id', $request->id)->first();

            return response()->json([
                'detail' => $detail
            ]);
        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to get loan application detail',
                'error' => $e->getMessage()
            ]);
        }
    }
}
