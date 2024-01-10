<?php

namespace App\Http\Controllers;

use App\Models\PosInventory;
use App\Models\PosProduct;
use App\Models\PosProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function createNewProduct(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'product_code' => 'required',
            'product_category' => 'required',
        ], [
            'product_name.required' => 'product name harus di isi',
            'product_code.required' => 'product price harus di isi',
            'product_category.required' => 'product category harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $product = new PosProduct();
        $product->profile_id = 1;
        $product->product_name = $request->product_name;
        $product->product_code = $request->product_code;
        $product->category_id = $request->product_category;
        $product->save();

        if (!$product) {
            return response()->json([
                'error' => "failed to create product"
            ], 500);
        }

        return response()->json($product, 200);
    }

    public function updateProduct(Request $request, $productID)
    {
        $product = PosProduct::where('profile_id', 1)->where('id', $request->product_id)->first();
        if (empty($product)) {
            return response()->json([
                'error' => "product not found"
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'product_code' => 'required',
            'product_category' => 'required',
        ], [
            'product_name.required' => 'product name harus di isi',
            'product_code.required' => 'product price harus di isi',
            'product_category.required' => 'product category harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $product->product_name = $request->product_name;
        $product->product_code = $request->product_code;
        $product->category_id = $request->product_category;
        $product->save();

        if (!$product) {
            return response()->json([
                'error' => "failed to update product"
            ], 500);
        }

        return response()->json($product, 200);
    }

    public function getSingleProduct($productID)
    {
        $product = PosProduct::with(['posCategory'])
            ->where('profile_id', 1)
            ->where('id', $productID)
            ->first();

        if (empty($product)) {
            return response()->json([
                'error' => "product not found"
            ], '404');
        }

        return response()->json($product, 200);
    }

    public function deleteProduct($productID)
    {
        $product = PosProduct::where('profile_id', 1)->where('id', $productID)->first();
        if (empty($product)) {
            return response()->json([
                'error' => "product not found"
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'product deleted'
        ], 200);
    }
}
