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

        $value = $request->produk;
        $listTest = [];
        $total = 0;

        for ($i = 0; $i < count($value); $i++) {

            $validator = Validator::make($value[$i], [
                'produk_id' => 'required',
                'qty' => 'required',
                'harga' => 'required',
                'stok_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 400);
            }

            array_push($listTest, [
                'pesanan_id' => $id,
                'produk_id' => $value[$i]['produk_id'],
                'qty' => $value[$i]['qty'],
                'harga' => $value[$i]['harga'],
            ]);

            $currentStock = Stok::find($value[$i]['stok_id']);
            if ($currentStock->jumlah_stok > 0) {
                $currentStock->decrement('jumlah_stok', $value[$i]['qty']);
            }
            $currentStock->save();
            $total += $value[$i]['harga'];
        }

        $detailPesanan = DB::table('detail_pesanan')->insert($listTest);

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

    public function createTransaksi(Request $request, $id)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'pesanan_id' => 'required',
            'tipe_pembayaran' => 'required',
            'status_pembayaran' => 'required',
            'diskon' => 'required',
            'subtotal_produk' => 'required',
            'subtotal_pengiriman' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $transaksi = new Transaksi;
        $transaksi->pesanan_id = $id;
        $transaksi->tipe_pembayaran = $request->tipe_pembayaran;
        $transaksi->status_pembayaran = $request->status_pembayaran;
        $transaksi->diskon = $request->diskon;
        $transaksi->subtotal_produk = $request->subtotal_produk;
        $transaksi->subtotal_pengiriman = $request->subtotal_pengiriman;
        $transaksi->save();

        if (!$transaksi) {
            return response()->json([
                'error' => 'failed to create transaksi'
            ], 500);
        }

        return response()->json([
            'message' => 'Transaksi berhasil dibuat',
            'data' => $transaksi
        ], 201);
    }

    public function getTransaksi($id)
    {
        $transaksi = DB::table('transaksi')
            ->join('pesanan', 'transaksi.pesanan_id', '=', 'pesanan.id')
            ->select('transaksi.*', 'pesanan.*', 'transaksi.id as tid', 'pesanan.id as pid')
            ->where('transaksi.id', '=', $id)
            ->first();

        if (empty($transaksi)) {
            return response()->json([
                'error' => 'Transaksi not found'
            ], '404');
        };

        return response()->json([
            'data' => $transaksi,
        ], 200);
    }

    public function getDetailTransaksi($id)
    {
        $transaksi = DB::table('transaksi')
            ->join('pesanan', 'pesanan.id', '=', 'transaksi.pesanan_id')
            ->join('users', 'users.id', '=', 'pesanan.user_id')
            ->join('alamat', 'alamat.id', '=', 'pesanan.alamat_id')
            ->join('kurir', 'kurir.id', '=', 'pesanan.kurir_id')
            ->where('transaksi.id', '=', $id)
            ->select('transaksi.*', 'pesanan.*', 'users.*', 'alamat.*', 'kurir.*', 'transaksi.id as tid', 'pesanan.id as pid', 'users.id as uid', 'alamat.id as aid', 'kurir.id as kid')
            ->first();

        $detailPesanan = DB::table('detail_pesanan')
            ->join('produk', 'produk.id', '=', 'detail_pesanan.produk_id')
            ->join('pesanan', 'pesanan.id', '=', 'detail_pesanan.pesanan_id')
            ->where('pesanan.id', '=', $transaksi->pesanan_id)
            ->select('detail_pesanan.*', 'produk.*', 'detail_pesanan.id as did', 'produk.id as prid')
            ->get();

        if (empty($transaksi)) {
            return response()->json([
                'error' => 'Transaksi not found'
            ], '404');
        };

        if (empty($detailPesanan)) {
            return response()->json([
                'error' => 'Detail Pesanan not found'
            ], '404');
        };

        return response()->json([
            'data' => [
                $transaksi,
                $detailPesanan
            ],
        ], 200);
    }

    public function getTransaksiListByUser($id)
    {
        $transaksi = DB::table('transaksi')
            ->join('pesanan', 'transaksi.pesanan_id', '=', 'pesanan.id')
            ->join('users', 'pesanan.user_id', '=', 'users.id')
            ->select('transaksi.*', 'pesanan.*', 'users.*', 'transaksi.id as tid', 'pesanan.id as pid','users.id as uid', 'transaksi.created_at as cat')
            ->orderBy('cat', 'desc')
            ->where('pesanan.user_id', '=', $id)
            ->get();

        if (empty($transaksi)) {
            return response()->json([
                'error' => 'Transaksi not found'
            ], '404');
        };

        return response()->json([
            'data' => $transaksi,
        ], 200);
    }
}
