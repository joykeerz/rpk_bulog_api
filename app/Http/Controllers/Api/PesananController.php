<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
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
            ->select('detail_pesanan.*', 'produk.*', 'detail_pesanan.id as did', 'produk.id as pid', 'detail_pesanan.created_at as cat')
            ->where('detail_pesanan.pesanan_id', '=', $pesanan->pid)
            ->orderBy('cat', 'desc')
            ->get();

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
            'status_pemesanan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $pesanan = Pesanan::create([
            'user_id' => $request->user_id,
            'alamat_id' => $request->alamat_id,
            'status_pemesanan' => 'pesanan belum dikonfirmasi',
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

        $validator = Validator::make($request->all(), [
            'pesanan_id' => 'required',
            'produk_id' => 'required',
            'qty' => 'required',
            'harga' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $detailPesanan = DetailPesanan::create([
            'pesanan_id' => $id,
            'produk_id' => $request->produk_id,
            'qty' => $request->qty,
            'harga' => $request->harga,
        ]);

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
