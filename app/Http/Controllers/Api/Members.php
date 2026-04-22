<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberDetail;
use Exception;

class Members extends Controller
{
    public function getActiveMembers()
    {
        try {
            $NumberOfActiveMembers = MemberDetail::where('status', 'Active')->count();

            return response()->json([
                'noa' => $NumberOfActiveMembers,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get active members',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
