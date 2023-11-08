<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    //
    public function getAllStocks()
    {
        $stocks = DB::table('stok')
            ->join('produk', 'stok.produk_id', '=', 'produk.id')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
            ->select('stok.*', 'produk.*', 'gudang.*', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'stok.created_at as cat')
            ->orderBy('cat', 'desc')
            // ->simplePaginate(1);
            ->get();

        if (empty($stocks)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stocks,
        ], 200);
    }

    public function getStockFromGudang($id){
        $stocks = DB::table('stok')
        ->join('produk', 'stok.produk_id', '=', 'produk.id')
        ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
        ->select('stok.*', 'produk.*', 'gudang.*', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'stok.created_at as cat')
        ->where('gudang.id', '=', $id)
        ->orderBy('cat', 'desc')
        // ->paginate(10);
        ->get();

        if (empty($stocks)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stocks,
        ], 200);
    }

    public function getStockFromProduct($id){
        $stocks = DB::table('stok')
        ->join('produk', 'stok.produk_id', '=', 'produk.id')
        ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
        ->select('stok.*', 'produk.*', 'gudang.*', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'stok.created_at as cat')
        ->where('produk.id', '=', $id)
        ->orderBy('cat', 'desc')
        // ->paginate(10);
        ->get();

        if (empty($stocks)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stocks,
        ], 200);
    }
}
