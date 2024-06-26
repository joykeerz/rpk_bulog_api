<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gudang;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    public function getAllStocks()
    {
        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
            ->join('kategori', 'kategori.id', '=', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', '=', 'produk.satuan_unit_id')
            ->join('pajak', 'pajak.id', '=', 'produk.pajak_id')
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
                'gudang.id as gudang_id',
                'stok_etalase.is_active',
                'stok_etalase.updated_at',
            )
            ->limit(20)
            ->simplePaginate(10);


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
        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
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
            ->simplePaginate(10);

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
        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
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
            ->where('stok.gudang_id', '=', $gid)
            ->simplePaginate(10);

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
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
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
            ->simplePaginate(10);

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
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
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
            ->simplePaginate(10);

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
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
            ->join('kategori', 'kategori.id', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', 'produk.satuan_unit_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select(
                'stok_etalase.id',
                'kategori.id as kategori_id',
                'produk.id as produk_id',
                'produk.nama_produk',
                'produk.produk_file_path',
                'produk.desk_produk',
                'stok_etalase.jumlah_stok',
                'stok.jumlah_stok as stok_gudang',
                'prices.price_value',
                'kategori.nama_kategori',
                'satuan_unit.nama_satuan',
                'pajak.nama_pajak',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang',
                'gudang.id as gudang_id',
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

    public function getSingleStockWithUser($id)
    {
        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
            ->join('kategori', 'kategori.id', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', 'produk.satuan_unit_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select(
                'stok_etalase.id',
                'kategori.id as kategori_id',
                'produk.id as produk_id',
                'produk.nama_produk',
                'produk.produk_file_path',
                'produk.desk_produk',
                'stok_etalase.jumlah_stok',
                'stok.jumlah_stok as stok_gudang',
                'prices.price_value',
                'kategori.nama_kategori',
                'satuan_unit.nama_satuan',
                'pajak.nama_pajak',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang',
                'gudang.id as gudang_id',
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

        $checkWishlist = Wishlist::where('user_id', Auth::user()->id)->where('stok_id', $stokEtalase->id)->first();
        if ($checkWishlist) {
            $stokEtalase->is_wishlisted = true;
        } else {
            $stokEtalase->is_wishlisted = false;
        }

        return response()->json([
            'data' => $stokEtalase,
        ], 200);
    }

    public function searchStockByProductName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string',
            'gudang_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
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
            ->where('gudang.id', '=', $request->gudang_id)
            ->where('produk.nama_produk', 'ilike', '%' . $request->nama_produk . '%')
            ->simplePaginate(10);

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
            'gudang_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
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
            ->where('gudang.id', '=', $request->gudang_id)
            ->where('kategori.nama_kategori', 'ilike', '%' . $request->nama_kategori . '%')
            ->simplePaginate(10);

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
        ], 200);
    }

    public function searchStockByCategoryNameNoLogin(Request $request)
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
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
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
            ->simplePaginate(10);

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
        ], 200);
    }

    public function searchStockByProductNameNoLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
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
            ->simplePaginate(10);

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
        ], 200);
    }

    public function getStockUnggulanFromGudangId($id)
    {
        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
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
                'stok_etalase.is_unggulan',
            )
            ->where('gudang.id', $id)
            ->where('stok_etalase.is_unggulan', true)
            ->simplePaginate(10);

        if (empty($stokEtalase)) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $stokEtalase,
        ], 200);
    }

    public function getAllStockUnggulan()
    {
        $stokEtalase = DB::table('stok_etalase')
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
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
                'stok_etalase.is_unggulan',
            )
            ->where('stok_etalase.is_unggulan', true)
            ->simplePaginate(10);

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
