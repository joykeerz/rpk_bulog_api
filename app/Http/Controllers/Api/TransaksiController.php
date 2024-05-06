<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\PosCategory;
use App\Models\PosDiscount;
use App\Models\PosInventory;
use App\Models\PosInventoryLog;
use App\Models\PosProduct;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    public function createTransaksi(Request $request, $id)
    {
        // return $request->all();
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'payment_option_id' => 'required',
            'status_pembayaran' => 'required',
            'subtotal_produk' => 'required',
            'subtotal_pengiriman' => 'required',
            'total_qty' => 'required',
            'total_dpp' => 'required',
            'total_ppn' => 'required',
            'dpp_terutang' => 'required',
            'ppn_terutang' => 'required',
            'dpp_dibebaskan' => 'required',
            'ppn_dibebaskan' => 'required',
            'kode_company' => 'required',
            'nomor_pembayaran' => 'required'
        ], [
            'payment_option_id.required' => 'payment option tidak boleh kosong',
            'status_pembayaran.required' => 'Status Pembayaran tidak boleh kosong',
            'subtotal_produk.required' => 'Subtotal Produk tidak boleh kosong',
            'subtotal_pengiriman.required' => 'Subtotal Pengiriman tidak boleh kosong',
            'total_qty.required' => 'Total Qty tidak boleh kosong',
            'total_dpp.required' => 'Total DPP tidak boleh kosong',
            'total_ppn.required' => 'Total PPN tidak boleh kosong',
            'dpp_terutang.required' => 'DPP Terutang tidak boleh kosong',
            'ppn_terutang.required' => 'PPN Terutang tidak boleh kosong',
            'dpp_dibebaskan.required' => 'DPP Dibebaskan tidak boleh kosong',
            'ppn_dibebaskan.required' => 'PPN Dibebaskan tidak boleh kosong',
            'kode_company.required' => 'Kode Company tidak boleh kosong',
            'nomor_pembayaran.required' => 'Nomor pembayaran tidak boleh kosong',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 200);
        }

        $transaksi = new Transaksi;
        $transaksi->pesanan_id = $id;
        $transaksi->tipe_pembayaran = DB::table('payment_options')
            ->where('payment_options.id', $request->payment_option_id)
            ->join('payment_types', 'payment_types.id', 'payment_options.payment_type_id')
            ->value('type_name')
            ->first();
        $transaksi->status_pembayaran = $request->status_pembayaran;
        $transaksi->subtotal_produk = $request->subtotal_produk;
        $transaksi->subtotal_pengiriman = $request->subtotal_pengiriman;
        $transaksi->total_qty = $request->total_qty;
        $transaksi->total_pembayaran = $request->subtotal_produk + $request->subtotal_pengiriman;
        $transaksi->kode_transaksi = 'ORD/' . $id . '/' . now()->format('m') . '/' . now()->format('Y') . '/' . $request->kode_company;
        $transaksi->total_dpp = $request->total_dpp;
        $transaksi->total_ppn = $request->total_ppn;
        $transaksi->dpp_terutang = $request->dpp_terutang;
        $transaksi->ppn_terutang = $request->ppn_terutang;
        $transaksi->dpp_dibebaskan = $request->dpp_dibebaskan;
        $transaksi->ppn_dibebaskan = $request->ppn_dibebaskan;
        $transaksi->nomor_pembayaran = $request->nomor_pembayaran;
        $transaksi->payment_option_id = $request->payment_option_id;
        $transaksi->save();

        if (!$transaksi) {
            return response()->json([
                'error' => 'failed to create transaksi'
            ], 500);
        }

        $this->addToPos($transaksi->id);
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
            ->select(
                'transaksi.pesanan_id',
                'transaksi.id as tid',
                'transaksi.tipe_pembayaran',
                'transaksi.status_pembayaran',
                'transaksi.diskon',
                'transaksi.subtotal_produk',
                'transaksi.subtotal_pengiriman',
                'transaksi.total_qty',
                'transaksi.total_pembayaran',
                'transaksi.kode_transaksi',
                'transaksi.total_dpp',
                'transaksi.total_ppn',
                'transaksi.dpp_terutang',
                'transaksi.ppn_terutang',
                'transaksi.dpp_dibebaskan',
                'transaksi.ppn_dibebaskan',
                'pesanan.user_id',
                'pesanan.alamat_id',
                'pesanan.kurir_id',
                'pesanan.gudang_id',
                'pesanan.status_pemesanan',
                'users.name',
                'alamat.jalan',
                'alamat.jalan_ext',
                'alamat.blok',
                'alamat.rt',
                'alamat.rw',
                'alamat.provinsi',
                'alamat.kota_kabupaten',
                'alamat.kecamatan',
                'alamat.kelurahan',
                'alamat.kode_pos',
                'kurir.nama_kurir',
                'transaksi.created_at as cat'
            )
            ->first();

        $detailPesanan = DB::table('detail_pesanan')
            ->join('produk', 'produk.id', '=', 'detail_pesanan.produk_id')
            ->join('pesanan', 'pesanan.id', '=', 'detail_pesanan.pesanan_id')
            ->join('kategori', 'kategori.id', '=', 'produk.kategori_id')
            ->join('satuan_unit', 'satuan_unit.id', '=', 'produk.satuan_unit_id')
            ->where('pesanan.id', '=', $transaksi->pesanan_id)
            ->select(
                'detail_pesanan.id as detail_pesanan_id',
                'detail_pesanan.pesanan_id',
                'detail_pesanan.produk_id',
                'detail_pesanan.qty',
                'detail_pesanan.harga',
                'detail_pesanan.dpp',
                'detail_pesanan.ppn',
                'detail_pesanan.jenis_pajak',
                'detail_pesanan.persentase_pajak',
                'detail_pesanan.subtotal_detail',
                'produk.kategori_id',
                'produk.pajak_id',
                'produk.satuan_unit_id',
                'produk.kode_produk',
                'produk.nama_produk',
                'produk.desk_produk',
                'produk.diskon_produk',
                'produk.produk_file_path',
            )
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
                'transaksi' => $transaksi,
                'detail_pesanan' => $detailPesanan
            ],
        ], 200);
    }

    public function getTransaksiListByUser($id)
    {

        $transaksi = DB::table('transaksi')
            ->join('pesanan', 'transaksi.pesanan_id', '=', 'pesanan.id')
            ->join('users', 'pesanan.user_id', '=', 'users.id')
            ->select(
                'transaksi.pesanan_id',
                'transaksi.tipe_pembayaran',
                'transaksi.status_pembayaran',
                'transaksi.diskon',
                'transaksi.subtotal_produk',
                'transaksi.subtotal_pengiriman',
                'transaksi.total_qty',
                'transaksi.total_pembayaran',
                'transaksi.kode_transaksi',
                'transaksi.total_dpp',
                'transaksi.total_ppn',
                'transaksi.dpp_terutang',
                'transaksi.ppn_terutang',
                'transaksi.dpp_dibebaskan',
                'transaksi.ppn_dibebaskan',
                'pesanan.user_id',
                'pesanan.alamat_id',
                'pesanan.kurir_id',
                'pesanan.status_pemesanan',
                'pesanan.gudang_id',
                'users.name',
                'transaksi.id as tid',
                'pesanan.id as pid',
                'users.id as uid',
                'transaksi.created_at as cat'
            )
            ->orderBy('cat', 'desc')
            ->where('pesanan.user_id', '=', $id)
            ->simplePaginate(10);

        if (empty($transaksi)) {
            return response()->json([
                'error' => 'Transaksi not found'
            ], 200);
        };

        return response()->json([
            'data' => $transaksi,
        ], 200);
    }

    public function addToPos($id)
    {
        $transaksi = Transaksi::find($id);
        $detailPesanan = DetailPesanan::where('pesanan_id', $transaksi->pesanan_id)->get();
        $profileId = Auth::user()->posProfile->id;

        // Start a database transaction
        DB::beginTransaction();

        try {
            foreach ($detailPesanan as $detail) {
                $productInfo = Produk::with('kategori')->find($detail->produk_id);

                $posCategory = PosCategory::firstOrNew(['category_name' => $productInfo->kategori->nama_kategori]);
                $posCategory->fill([
                    'profile_id' => $profileId,
                    'category_desc' => $productInfo->kategori->deskripsi_kategori,
                    'is_from_bulog' => true,
                ])->save();

                $posProduct = PosProduct::firstOrNew(['product_code' => $productInfo->kode_produk]);
                $posProduct->fill([
                    'profile_id' => $profileId,
                    'category_id' => $posCategory->id,
                    'product_name' => $productInfo->nama_produk,
                    'product_desc' => $productInfo->deskripsi_produk,
                    'is_from_bulog' => true,
                ])->save();

                $posInventory = PosInventory::updateOrCreate(
                    ['product_id' => $posProduct->id],
                    [
                        'discount_id' => PosDiscount::where('profile_id', $profileId)
                            ->where('discount_name', 'Tidak Diskon')
                            ->value('id'),
                        'quantity' => DB::raw('quantity + ' . $detail->qty),
                        'price' => $detail->harga,
                        'is_from_bulog' => true
                    ]
                );

                $posInventoryLog = new PosInventoryLog();
                $posInventoryLog->fill([
                    'pos_inventory_id' => $posInventory->id,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                    'quantity' => $detail->qty,
                    'io_status' => 'in',
                    'io_date' => now(),
                ])->save();
            }

            // Commit the transaction if everything is successful
            DB::commit();

            return response()->json("Transaksi Berhasil", 200);
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollback();
            return response()->json("Gagal menyimpan $e", 200);

            // Handle or log the exception
            // You can throw the exception again if you want to propagate it
        }
    }
}
