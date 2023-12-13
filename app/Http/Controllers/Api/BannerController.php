<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banner = Banner::take(5)->orderBy('created_at', 'desc')->get();
        if (empty($banner) || $banner->count() < 1) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };
        return response()->json([
            'data' => $banner,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $banner = Banner::find($id);
        if (empty($banner) || $banner->count() < 1) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };
        return response()->json([
            'data' => $banner,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
