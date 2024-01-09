<?php

namespace App\Http\Controllers;

use App\Models\PosCategory;
use App\Models\PosProfile;
use Illuminate\Http\Request;

class PosCategoryController extends Controller
{
    //
    public function getUserCategory()
    {
        $category = PosCategory::where('profile_id', 1)->get();

        if (empty($category)) {
            return response()->json([
                'error' => "there's no data yet"
            ], 404);
        };

        return response()->json($category, 200);
    }
}
