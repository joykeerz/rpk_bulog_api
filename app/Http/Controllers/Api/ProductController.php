<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    //
    public function getProducts()
    {
        $products = DB::table('produk')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->select('produk.*', 'kategori.*', 'kategori.id as kid', 'produk.id as pid', 'produk.created_at as cat')
            ->orderBy('cat', 'desc')
            // ->paginate(10);
            ->get();

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
        $categories = Kategori::all();

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
            ->get();

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
        $search = $request->search;

        $products = DB::table('produk')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->select('produk.*', 'kategori.*', 'kategori.id as kid', 'produk.id as pid', 'produk.created_at as cat')
            ->where('produk.nama_produk', 'like', "%" . $search . "%")
            ->orWhere('kategori.nama_kategori', 'like', "%" . $search . "%")
            ->orderBy('produk.created_at', 'desc')
            ->get();

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
        $search = $request->search;

        $categories = DB::table('kategori')
            ->select('kategori.*', 'kategori.id as kid', 'kategori.created_at as cat')
            ->where('kategori.nama_kategori', 'like', "%" . $search . "%")
            // ->latest()
            ->orderBy('cat', 'desc')
            ->get();

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
        $request->validate([
            'nama_kategori' => 'required',
            'deskripsi_kategori' => 'required',
        ]);

        $category = DB::table('kategori')->insert([
            'nama_kategori' => $request->nama_kategori,
            'deskripsi_kategori' => $request->deskripsi_kategori,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!$category) {
            return response()->json([
                'error' => "failed to add new category"
            ], '404');
        };

        return response()->json([
            'message' => "success add new category",
            'data' => $category,
        ], 200);
    }

    public function createProduct(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required',
            'kode_produk' => 'required',
            'nama_produk' => 'required',
            'desk_produk' => 'required',
            'harga_produk' => 'required',
            'diskon_produk' => 'required',
            'satuan_unit_produk' => 'required',
        ]);

        $product = DB::table('produk')->insert([
            'kategori_id' => $request->kategori_id,
            'kode_produk' => $request->kode_produk,
            'nama_produk' => $request->nama_produk,
            'desk_produk' => $request->desk_produk,
            'harga_produk' => $request->harga_produk,
            'diskon_produk' => $request->diskon_produk,
            'satuan_unit_produk' => $request->satuan_unit_produk,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!$product) {
            return response()->json([
                'error' => "failed to add new product"
            ], '404');
        };

        return response()->json([
            'message' => "success add new product",
            'data' => $product,
        ], 200);
    }
}
