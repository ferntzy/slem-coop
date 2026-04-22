<?php

namespace App\Http\Controllers;

use App\Models\HeroNewsEvent;
use Exception;

class HeroNewsEventController extends Controller
{
    public function show()
    {
        try {
            if (HeroNewsEvent::getHeroNewsEvent()) {
                $n = HeroNewsEvent::all();

                return response()->json([
                    'data' => $n,
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
