<?php

namespace App\Http\Controllers;

use App\Models\PosCategory;
use App\Models\PosProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function createCategory(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'category_name' => 'required',
        ], [
            'category_name.required' => 'category name harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $category = new PosCategory();
        $category->profile_id = 1;
        $category->category_name = $request->category_name;
        $category->category_desc = $request->category_desc;
        $category->save();

        if (!$category) {
            return response()->json([
                'error' => "failed to create category"
            ], 500);
        }
        return response()->json($category, 200);
    }
}
