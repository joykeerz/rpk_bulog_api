<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Stok;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PesananController extends Controller
{
    public function getPesananUser($id)
    {
        $pesanan = DB::table('pesanan')
            ->join('users', 'pesanan.user_id', '=', 'users.id')
            ->join('alamat', 'pesanan.alamat_id', '=', 'alamat.id')
            ->select('pesanan.*', 'users.*', 'alamat.*', 'pesanan.id as pid', 'users.id as uid', 'alamat.id as aid')
            ->where('pesanan.user_id', '=', $id)
            ->first();

        $detailPesanan = DB::table('detail_pesanan')
            ->join('produk', 'detail_pesanan.produk_id', '=', 'produk.id')
            ->select('detail_pesanan.*', 'produk.*', 'detail_pesanan.id as did', 'produk.id as prid', 'detail_pesanan.created_at as cat')
            ->where('detail_pesanan.pesanan_id', '=', $pesanan->pid)
            ->orderBy('cat', 'desc')
            ->simplePaginate(10);

        if (empty($pesanan)) {
            return response()->json([
                'error' => 'Pesanan not found'
            ], '404');
        };

        return response()->json([
            'data' => [
                $pesanan,
                $detailPesanan
            ],
        ], 200);
    }

    public function createPesanan(Request $request)
    {

        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'alamat_id' => 'required',
            'kurir_id' =>   'required',
            'gudang_id' =>   'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 200);
        }

        $pesanan = Pesanan::create([
            'user_id' => $request->user_id,
            'alamat_id' => $request->alamat_id,
            'kurir_id' => $request->kurir_id,
            'gudang_id' => $request->gudang_id,
            'status_pemesanan' => 'belum dibayar',
        ]);

        if (!$pesanan) {
            return response()->json([
                'error' => 'failed to create pesanan'
            ], 500);
        }

        return response()->json([
            'message' => 'Pesanan berhasil dibuat',
            'data' => $pesanan
        ], 201);
    }

    public function createDetailPesanan(Request $request, $id)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $inputProduct = $request->produk;
        $listProduct = [];
        $total = 0;

        for ($i = 0; $i < count($inputProduct); $i++) {

            $validator = Validator::make($inputProduct[$i], [
                'produk_id' => 'required',
                'qty' => 'required',
                'harga' => 'required',
                'stok_id' => 'required',
                'dpp' => 'required',
                'ppn' => 'required',
                'jenis_pajak' => 'required',
                'persentase_pajak' => 'required',
                'subtotal_detail' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 200);
            }

            array_push($listProduct, [
                'pesanan_id' => $id,
                'produk_id' => $inputProduct[$i]['produk_id'],
                'qty' => $inputProduct[$i]['qty'],
                'harga' => $inputProduct[$i]['harga'],
                'dpp' => $inputProduct[$i]['dpp'],
                'ppn' => $inputProduct[$i]['ppn'],
                'jenis_pajak' => $inputProduct[$i]['jenis_pajak'],
                'persentase_pajak' => $inputProduct[$i]['persentase_pajak'],
                'subtotal_detail' => $inputProduct[$i]['subtotal_detail'],
            ]);

            $currentStock = Stok::find($inputProduct[$i]['stok_id']);
            if ($currentStock->jumlah_stok == 0 || $currentStock->jumlah_stok < $inputProduct[$i]['qty']) {
                return response()->json([
                    'error' => "Stok id $currentStock->produk_id tidak mencukupi"
                ], 400);
            }
            $currentStock->decrement('jumlah_stok', $inputProduct[$i]['qty']);
            $currentStock->save();
            $total += $inputProduct[$i]['subtotal_detail'];
        }

        $detailPesanan = DB::table('detail_pesanan')->insert($listProduct);

        if (!$detailPesanan) {
            return response()->json([
                'error' => 'failed to create detail pesanan'
            ], 500);
        }

        return response()->json([
            'message' => 'Detail pesanan berhasil dibuat',
            'data' => $detailPesanan
        ], 201);
    }
}
