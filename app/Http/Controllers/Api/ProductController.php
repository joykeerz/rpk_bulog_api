<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    //
    public function getProducts()
    {
        $products = DB::table('produk')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->select('produk.*', 'kategori.*', 'kategori.id as kid', 'produk.id as pid', 'produk.created_at as cat')
            ->orderBy('cat', 'desc')
            ->simplePaginate(10);

        if (empty($products)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $products,
        ], 200);
    }

    public function getCategories()
    {
        $categories = Kategori::simplePaginate(15);

        if (empty($categories)) {
            return response()->json([
                'error' => "There's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $categories,
        ], 200);
    }

    public function getProduct($id)
    {
        $product = DB::table('produk')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->select('kategori.*', 'produk.*', 'kategori.id as kid', 'produk.id as pid')
            ->where('produk.id', '=', $id)
            ->first();

        if (empty($product)) {
            return response()->json([
                'error' => 'Product not found'
            ], '404');
        };

        return response()->json([
            'data' => $product,
        ], 200);
    }

    public function getProductFromCategory($id)
    {
        $products = DB::table('produk')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->select('produk.*', 'kategori.*', 'kategori.id as kid', 'produk.id as pid', 'produk.created_at as cat')
            ->where('kategori.id', '=', $id)
            ->orderBy('cat', 'desc')
            ->simplePaginate(10);

        if (empty($products)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $products,
        ], 200);
    }

    public function searchProduct(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $search = $request->search;

        $products = DB::table('produk')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->select('produk.*', 'kategori.*', 'kategori.id as kid', 'produk.id as pid', 'produk.created_at as cat')
            ->where('produk.nama_produk', 'like', "%" . $search . "%")
            ->orWhere('kategori.nama_kategori', 'ilike', "%" . $search . "%")
            ->orderBy('produk.created_at', 'desc')
            ->simplePaginate(10);

        if (!$products) {
            return response()->json([
                'error' => "sorry your search didn't match any data"
            ], '404');
        };

        return response()->json([
            'data' => $products,
        ], 200);
    }

    public function searchCategory(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $search = $request->search;

        $categories = DB::table('kategori')
            ->select('kategori.*', 'kategori.id as kid', 'kategori.created_at as cat')
            ->where('kategori.nama_kategori', 'ilike', "%" . $search . "%")
            ->orderBy('cat', 'desc')
            ->simplePaginate(10);

        if (!$categories) {
            return response()->json([
                'error' => "sorry your search didn't match any data"
            ], '404');
        };

        return response()->json([
            'data' => $categories,
        ], 200);
    }

    public function createCategory(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required',
            'deskripsi_kategori' => 'required',
        ]);

        $category = Kategori::create([
            'nama_kategori' => $request->nama_kategori,
            'deskripsi_kategori' => $request->deskripsi_kategori,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => [],
                'message' => $validator->errors(),
                'success' => false
            ]);
        }

        if (!$category) {
            return response()->json([
                'error' => "failed to add new category"
            ], 400);
        };

        return response()->json([
            'message' => "success add new category",
            'data' => $category,
        ], 200);
    }

    public function createProduct(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'kategori_id' => 'required',
            'kode_produk' => 'required',
            'nama_produk' => 'required',
            'desk_produk' => 'required',
            'harga_produk' => 'required',
            'diskon_produk' => 'required',
            'satuan_unit_produk' => 'required',
        ]);

        $product = Produk::create([
            'kategori_id' => $request->kategori_id,
            'kode_produk' => $request->kode_produk,
            'nama_produk' => $request->nama_produk,
            'desk_produk' => $request->desk_produk,
            'harga_produk' => $request->harga_produk,
            'diskon_produk' => $request->diskon_produk,
            'satuan_unit_produk' => $request->satuan_unit_produk,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => [],
                'message' => $validator->errors(),
                'success' => false
            ]);
        }

        if (!$product) {
            return response()->json([
                'error' => "failed to add new product"
            ], 400);
        };

        return response()->json([
            'message' => "success add new product",
            'data' => $product,
        ], 200);
    }

    private function requestChecker(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }
        return $request;
    }
}
