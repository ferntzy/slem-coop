<?php

namespace App\Http\Controllers;

use App\Models\News;
use Exception;

class NewsController extends Controller
{
    public function show()
    {
        try{
            if(News::getNews()){
            $n = News::all();

            return response()->json([
                'message' => 'News retrieved successfully',
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
