<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    public function getAllStocks()
    {
        // return $satuan_unit = DB::table('satuan_unit')
        //     ->select('satuan_unit.*')
        //     ->get();

        // $stocks = DB::table('stok')
        //     ->join('produk', 'stok.produk_id', '=', 'produk.id')
        //     ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
        //     ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
        //     ->join('satuan_unit', 'produk.satuan_unit_id', '=', 'satuan_unit.id')
        //     ->join('pajak', 'produk.pajak_id', '=', 'pajak.id')
        //     ->select('stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'pajak.jenis_pajak', 'pajak.nama_pajak', 'pajak.persentase_pajak', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
        //     ->orderBy('cat', 'desc')
        //     // ->simplePaginate(1);
        //     ->get();

        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('prices', 'prices.id', 'stok.id')
            ->join('produk', 'produk.id', 'stok.produk_id')
            ->join('gudang', 'gudang.id', 'stok.gudang_id')
            ->join('kategori', 'kategori.id', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', 'produk.satuan_unit_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select(
                'stok_etalase.id',
                'kategori.id as kategori_id',
                'produk.id as produk_id',
                'produk.nama_produk',
                'produk.produk_file_path',
                'stok_etalase.jumlah_stok',
                'stok.jumlah_stok as stok_gudang',
                'prices.price_value',
                'kategori.nama_kategori',
                'satuan_unit.nama_satuan',
                'pajak.nama_pajak',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang',
                'stok_etalase.is_active',
                'stok_etalase.updated_at',
            )
            ->limit(20)
            ->get();

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
        ], 200);
    }

    public function getStocksByCategory($id)
    {
        // $stocks = DB::table('stok')
        //     ->join('produk', 'stok.produk_id', '=', 'produk.id')
        //     ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
        //     ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
        //     ->join('satuan_unit', 'produk.satuan_unit_id', '=', 'satuan_unit.id')
        //     ->join('pajak', 'produk.pajak_id', '=', 'pajak.id')
        //     ->select('satuan_unit.satuan_unit_produk', 'stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'pajak.jenis_pajak', 'pajak.nama_pajak', 'pajak.persentase_pajak', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
        //     ->where('produk.kategori_id', '=', $id)
        //     ->orderBy('cat', 'desc')
        //     // ->paginate(10);
        //     ->get();

        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('prices', 'prices.id', 'stok.id')
            ->join('produk', 'produk.id', 'stok.produk_id')
            ->join('gudang', 'gudang.id', 'stok.gudang_id')
            ->join('kategori', 'kategori.id', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', 'produk.satuan_unit_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select(
                'stok_etalase.id',
                'kategori.id as kategori_id',
                'produk.id as produk_id',
                'produk.nama_produk',
                'produk.produk_file_path',
                'stok_etalase.jumlah_stok',
                'stok.jumlah_stok as stok_gudang',
                'prices.price_value',
                'kategori.nama_kategori',
                'satuan_unit.nama_satuan',
                'pajak.nama_pajak',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang',
                'stok_etalase.is_active',
                'stok_etalase.updated_at',
            )
            ->where('produk.kategori_id', '=', $id)
            ->get();

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
        ], 200);
    }

    public function getStocksByCategoryAndGudang($id, $gid)
    {
        // return $id .' '. $gid;
        // $stocks = DB::table('stok')
        //     ->join('produk', 'stok.produk_id', '=', 'produk.id')
        //     ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
        //     ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
        //     ->join('satuan_unit', 'produk.satuan_unit_id', '=', 'satuan_unit.id')
        //     ->join('pajak', 'produk.pajak_id', '=', 'pajak.id')
        //     ->select('satuan_unit.satuan_unit_produk', 'stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'pajak.jenis_pajak', 'pajak.nama_pajak', 'pajak.persentase_pajak', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
        //     ->where('produk.kategori_id', '=', $id)
        //     ->where('gudang.id', '=', $gid)
        //     ->orderBy('cat', 'desc')
        //     // ->paginate(10);
        //     ->get();

        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('prices', 'prices.id', 'stok.id')
            ->join('produk', 'produk.id', 'stok.produk_id')
            ->join('gudang', 'gudang.id', 'stok.gudang_id')
            ->join('kategori', 'kategori.id', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', 'produk.satuan_unit_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select(
                'stok_etalase.id',
                'kategori.id as kategori_id',
                'produk.id as produk_id',
                'produk.nama_produk',
                'produk.produk_file_path',
                'stok_etalase.jumlah_stok',
                'stok.jumlah_stok as stok_gudang',
                'prices.price_value',
                'kategori.nama_kategori',
                'satuan_unit.nama_satuan',
                'pajak.nama_pajak',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang',
                'stok_etalase.is_active',
                'stok_etalase.updated_at',
            )
            ->where('produk.kategori_id', '=', $id)
            ->where('gudang.id', '=', $gid)
            ->get();

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
        ], 200);
    }

    public function getStockFromGudang($id)
    {
        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('prices', 'prices.id', 'stok.id')
            ->join('produk', 'produk.id', 'stok.produk_id')
            ->join('gudang', 'gudang.id', 'stok.gudang_id')
            ->join('kategori', 'kategori.id', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', 'produk.satuan_unit_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select(
                'stok_etalase.id',
                'kategori.id as kategori_id',
                'produk.id as produk_id',
                'produk.nama_produk',
                'produk.produk_file_path',
                'stok_etalase.jumlah_stok',
                'stok.jumlah_stok as stok_gudang',
                'prices.price_value',
                'kategori.nama_kategori',
                'satuan_unit.nama_satuan',
                'pajak.nama_pajak',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang',
                'stok_etalase.is_active',
                'stok_etalase.updated_at',
            )
            ->where('gudang.id', $id)
            ->get();

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
        ], 200);
    }

    public function getStockFromProduct($id)
    {
        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('produk', 'produk.id', 'stok.produk_id')
            ->join('gudang', 'gudang.id', 'stok.gudang_id')
            ->join('kategori', 'kategori.id', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', 'produk.satuan_unit_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select(

                'stok_etalase.id',
                'kategori.id as kategori_id',
                'produk.id as produk_id',
                'produk.nama_produk',
                'produk.produk_file_path',
                'stok_etalase.jumlah_stok',
                'stok.jumlah_stok as stok_gudang',
                'kategori.nama_kategori',
                'satuan_unit.nama_satuan',
                'pajak.nama_pajak',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang',
                'stok_etalase.is_active',
                'stok_etalase.updated_at',
            )
            ->where('produk.id', '=', $id)
            ->get();

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
        ], 200);
    }

    public function getSingleStock($id)
    {
        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('produk', 'produk.id', 'stok.produk_id')
            ->join('gudang', 'gudang.id', 'stok.gudang_id')
            ->join('kategori', 'kategori.id', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', 'produk.satuan_unit_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select(

                'stok_etalase.id',
                'kategori.id as kategori_id',
                'produk.id as produk_id',
                'produk.nama_produk',
                'produk.produk_file_path',
                'stok_etalase.jumlah_stok',
                'stok.jumlah_stok as stok_gudang',
                'kategori.nama_kategori',
                'satuan_unit.nama_satuan',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang',
                'stok_etalase.is_active',
                'stok_etalase.updated_at',
            )
            ->where('stok_etalase.id', '=', $id)
            ->first();

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
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

        // $stocks = DB::table('stok')
        //     ->join('produk', 'stok.produk_id', '=', 'produk.id')
        //     ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
        //     ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
        //     ->select('stok.*', 'produk.*', 'gudang.*', 'kategori.*', 'stok.id as sid', 'produk.id as pid', 'gudang.id as gid', 'kategori.id as kid', 'stok.created_at as cat')
        //     ->where('produk.nama_produk', 'like', '%' . $request->nama_produk . '%')
        //     ->orderBy('cat', 'desc')
        //     // ->paginate(10);
        //     ->get();

        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('prices', 'prices.id', 'stok.id')
            ->join('produk', 'produk.id', 'stok.produk_id')
            ->join('gudang', 'gudang.id', 'stok.gudang_id')
            ->join('kategori', 'kategori.id', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', 'produk.satuan_unit_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select(
                'stok_etalase.id',
                'kategori.id as kategori_id',
                'produk.id as produk_id',
                'produk.nama_produk',
                'produk.produk_file_path',
                'stok_etalase.jumlah_stok',
                'stok.jumlah_stok as stok_gudang',
                'prices.price_value',
                'kategori.nama_kategori',
                'satuan_unit.nama_satuan',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang',
                'stok_etalase.is_active',
                'stok_etalase.updated_at',
            )
            ->where('produk.nama_produk', 'ilike', '%' . $request->nama_produk . '%')
            ->get();

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
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

        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('prices', 'prices.id', 'stok.id')
            ->join('produk', 'produk.id', 'stok.produk_id')
            ->join('gudang', 'gudang.id', 'stok.gudang_id')
            ->join('kategori', 'kategori.id', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', 'produk.satuan_unit_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select(
                'stok_etalase.id',
                'kategori.id as kategori_id',
                'produk.id as produk_id',
                'produk.nama_produk',
                'produk.produk_file_path',
                'stok_etalase.jumlah_stok',
                'stok.jumlah_stok as stok_gudang',
                'prices.price_value',
                'kategori.nama_kategori',
                'satuan_unit.nama_satuan',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang',
                'stok_etalase.is_active',
                'stok_etalase.updated_at',
            )
            ->where('kategori.nama_kategori', 'ilike', '%' . $request->nama_kategori . '%')
            ->get();

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
        ], 200);
    }
}
