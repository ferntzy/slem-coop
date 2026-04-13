<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NewsEvent;
use Exception;

class NewsEventController extends Controller
{
    public function show()
    {
        try{
            if(NewsEvent::getNewsEvent()){
            $n = NewsEvent::all();

            return response()->json([
                'message' => 'News Events retrieved successfully',
                'data' => $n,
                'status' => 200
            ]);
            }
        }catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
