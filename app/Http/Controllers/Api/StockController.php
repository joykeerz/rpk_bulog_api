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
        // return $satuan_unit = DB::table('satuan_unit')
        //     ->select('satuan_unit.*')
        //     ->get();

        $stocks = DB::table('stok')
            ->join('produk', 'stok.produk_id', '=', 'produk.id')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->join('satuan_unit', 'produk.satuan_unit_id', '=', 'satuan_unit.id')
            ->join('pajak', 'produk.pajak_id', '=', 'pajak.id')
            ->select('stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'pajak.jenis_pajak', 'pajak.nama_pajak', 'pajak.persentase_pajak', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
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

    public function getStocksByCategory($id)
    {
        $stocks = DB::table('stok')
            ->join('produk', 'stok.produk_id', '=', 'produk.id')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->join('satuan_unit', 'produk.satuan_unit_id', '=', 'satuan_unit.id')
            ->join('pajak', 'produk.pajak_id', '=', 'pajak.id')
            ->select('satuan_unit.satuan_unit_produk', 'stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'pajak.jenis_pajak', 'pajak.nama_pajak', 'pajak.persentase_pajak', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
            ->where('produk.kategori_id', '=', $id)
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

    public function getStocksByCategoryAndGudang($id, $gid)
    {
        // return $id .' '. $gid;
        $stocks = DB::table('stok')
            ->join('produk', 'stok.produk_id', '=', 'produk.id')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->join('satuan_unit', 'produk.satuan_unit_id', '=', 'satuan_unit.id')
            ->join('paajak', 'produk.pajak_id', '=', 'pajak.id')
            ->select('satuan_unit.satuan_unit_produk', 'stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'pajak.jenis_pajak', 'pajak.nama_pajak', 'pajak.persentase_pajak', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
            ->where('produk.kategori_id', '=', $id)
            ->where('gudang.id', '=', $gid)
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

    public function getStockFromGudang($id)
    {
        $stocks = DB::table('stok')
            ->join('produk', 'stok.produk_id', '=', 'produk.id')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->join('satuan_unit', 'produk.satuan_unit_id', '=', 'satuan_unit.id')
            ->join('pajak', 'produk.pajak_id', '=', 'pajak.id')
            ->select('stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'pajak.jenis_pajak', 'pajak.nama_pajak', 'pajak.persentase_pajak', 'satuan_unit.satuan_unit_produk', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
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

    public function getStockFromProduct($id)
    {
        $stocks = DB::table('stok')
            ->join('produk', 'stok.produk_id', '=', 'produk.id')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->join('satuan_unit', 'produk.satuan_unit_id', '=', 'satuan_unit.id')
            ->join('pajak', 'produk.pajak_id', '=', 'pajak.id')
            ->select('satuan_unit.satuan_unit_produk', 'stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'pajak.jenis_pajak', 'pajak.nama_pajak', 'pajak.persentase_pajak', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
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

    public function getSingleStock($id)
    {
        $stock = DB::table('stok')
            ->join('produk', 'stok.produk_id', '=', 'produk.id')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->join('satuan_unit', 'produk.satuan_unit_id', '=', 'satuan_unit.id')
            ->join('pajak', 'produk.pajak_id', '=', 'pajak.id')
            ->select('satuan_unit.satuan_unit_produk', 'stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'pajak.jenis_pajak', 'pajak.nama_pajak', 'pajak.persentase_pajak', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
            ->where('stok.id', '=', $id)
            ->orderBy('cat', 'desc')
            // ->paginate(10);
            ->first();

        if (empty($stock)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stock,
        ], 200);
    }

    public function searchStockByProductName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $stocks = DB::table('stok')
            ->join('produk', 'stok.produk_id', '=', 'produk.id')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->select('stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
            ->where('produk.nama_produk', 'like', '%' . $request->nama_produk . '%')
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

    public function searchStockByCategoryName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $stocks = DB::table('stok')
            ->join('produk', 'stok.produk_id', '=', 'produk.id')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->select('stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
            ->where('kategori.nama_kategori', 'like', '%' . $request->nama_kategori . '%')
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
