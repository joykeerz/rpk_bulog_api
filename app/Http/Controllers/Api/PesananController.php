<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\PosCategory;
use App\Models\PosDiscount;
use App\Models\PosInventory;
use App\Models\PosInventoryLog;
use App\Models\PosProduct;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\StokEtalase;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'nama_penerima' =>   'required',
            'created_at' =>   'required',
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
            'status_pemesanan' => 'menunggu verifikasi',
            'nama_penerima' => $request->nama_penerima,
            'created_at' => $request->created_at
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
                'stok_etalase_id' => 'required',
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

            $currentStock = StokEtalase::where('id', $inputProduct[$i]['stok_etalase_id'])->first();
            if ($currentStock->jumlah_stok == 0 || $currentStock->jumlah_stok < $inputProduct[$i]['qty']) {
                return response()->json([
                    'error' => "Stok $currentStock->produk_id tidak mencukupi"
                ], 400);
            }
            // $currentStock->decrement('jumlah_stok', $inputProduct[$i]['qty']);
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
        ], 200);
    }

    public function changeStatusDiterima($id)
    {
        $pesanan = Pesanan::find($id);
        $pesanan->status_pemesanan = 'diterima';
        $pesanan->is_confirmed = true;
        $pesanan->save();

        $transaksi = Transaksi::where('pesanan_id', $id)->first();
        $detailPesanan = DetailPesanan::where('pesanan_id', $transaksi->pesanan_id)->get();
        $profileId = Auth::user()->posProfile->id;

        // if (!$profileId) {
        //     return response()->json('user tidak memiliki akun PoS. tidak menambahkan inventory PoS', 200);
        // }

        // Start a database transaction
        DB::beginTransaction();

        foreach ($detailPesanan as $detail) {
            $productInfo = Produk::with('kategori')->find($detail->produk_id);

            if ($productInfo) {
                Log::info('$productInfo ditemukan');
            }

            $posCategory = PosCategory::firstOrNew(['category_name' => $productInfo->kategori->nama_kategori]);
            $posCategory->fill([
                'profile_id' => $profileId,
                'category_desc' => $productInfo->kategori->deskripsi_kategori,
                'is_from_bulog' => true,
            ])->save();

            if ($posCategory) {
                Log::info('$posCategory berhasil dibuat');
            }

            $posProduct = PosProduct::firstOrNew(['product_code' => $productInfo->kode_produk]);
            $posProduct->fill([
                'profile_id' => $profileId,
                'category_id' => $posCategory->id,
                'product_name' => $productInfo->nama_produk,
                'product_desc' => $productInfo->deskripsi_produk ? $productInfo->deskripsi_produk : 'tidak ada',
                'product_image' => $productInfo->produk_file_path ? $productInfo->produk_file_path : 'images/product/default.png',
                'is_from_bulog' => true,
            ])->save();

            if ($posProduct) {
                Log::info('$posProduct berhasil dibuat');
            }

            $posInventory = PosInventory::updateOrCreate(
                ['product_id' => $posProduct->id],
                [
                    'discount_id' => PosDiscount::where('profile_id', $profileId)
                        ->where('discount_name', 'Tidak Diskon')
                        ->value('id'),
                    'price' => $detail->harga,
                    'is_from_bulog' => true
                ]
            );
            $posInventory->increment('quantity', $detail->qty);

            if ($posInventory) {
                Log::info('$posInventory berhasil dibuat/update');
            }

            $posInventoryLog = new PosInventoryLog();
            $posInventoryLog->fill([
                'pos_inventory' => $posInventory->id,
                'kode_transaksi' => $transaksi->kode_transaksi,
                'quantity' => $detail->qty,
                'io_status' => 'in',
                'io_date' => now(),
            ])->save();

            if ($posInventoryLog) {
                Log::info('$posInventoryLog berhasil dibuat');
            }
        }

        // Commit the transaction if everything is successful
        DB::commit();

        if (!$posInventoryLog) {
            // Rollback the transaction if an error occurs
            DB::rollback();
            return response()->json("Gagal menyimpan", 200);
        }
        return response()->json("Pesanan diterima. Transaksi Berhasil", 200);
    }
}
