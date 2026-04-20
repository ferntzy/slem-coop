<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Exception;
use Illuminate\Http\Request;

class Members extends Controller
{
    public function getActiveMembers(){
        try{
            $NumberOfActiveMembers = Member::where('status', 'Active')->count();

            return response()->json([
                'noa' => $NumberOfActiveMembers
            ]);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Unable to get active members',
                'error' => $e->getMessage()
            ]);
        }
    }
}
