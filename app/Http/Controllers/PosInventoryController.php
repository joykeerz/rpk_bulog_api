<?php

namespace App\Http\Controllers;

use App\Models\PosInventory;
use App\Models\PosProduct;
use App\Models\PosProfile;
use Illuminate\Http\Request;

class PosInventoryController extends Controller
{
    //
    public function getUserInventory()
    {
        // $inventory = PosProfile::with(['posProduct.posInventory', 'posProduct.posCategory'])->get();
        $inventory = PosProduct::with(['posInventory', 'posCategory'])
            ->where('profile_id', 1)
            ->get();

        if (empty($inventory)) {
            return response()->json([
                'error' => "there's no data yet"
            ], 404);
        };

        return response()->json($inventory, 200);
    }

    public function getUserProducts()
    {
        $products = PosProduct::with(['posCategory'])
            ->where('profile_id', 1)
            ->get();
        if (empty($products)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json($products, 200);
    }
}
