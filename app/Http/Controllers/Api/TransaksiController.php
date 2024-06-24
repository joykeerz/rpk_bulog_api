<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\DetailPesanan;
use App\Models\PosCategory;
use App\Models\PosDiscount;
use App\Models\PosInventory;
use App\Models\PosInventoryLog;
use App\Models\PosProduct;
use App\Models\Produk;
use App\Models\StokEtalase;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    public function createDetailPesananComplete($pesananId)
    {
        $carts = DB::table('carts')
            ->join('stok_etalase', 'stok_etalase.id', 'carts.stok_id')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('gudang', 'carts.gudang_id', 'gudang.id')
            ->join('produk', 'stok.produk_id', 'produk.id')
            ->join('pajak', 'produk.pajak_id', 'pajak.id')
            ->where('carts.user_id', '=', Auth::user()->id)
            ->select(
                'carts.id as cart_id',
                'stok.id as stok_id',
                'stok_etalase.id as stok_etalase_id',
                'produk.id as product_id',
                'gudang.id as gudang_id',
                'produk.nama_produk as nama',
                'prices.price_value as harga',
                'carts.quantity',
                'produk.produk_file_path as image',
                'carts.dpp',
                'carts.ppn',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'carts.subtotal_detail',
                'gudang.nama_gudang'
            )
            ->get();
        $listProduct = [];
        $total = 0;

        foreach ($carts as $key => $cart) {
            array_push($listProduct, [
                'pesanan_id' => $pesananId,
                'produk_id' => $cart->product_id,
                'qty' => $cart->quantity,
                'harga' => $cart->harga,
                'dpp' => $cart->dpp,
                'ppn' => $cart->ppn,
                'jenis_pajak' => $cart->jenis_pajak,
                'persentase_pajak' => $cart->persentase_pajak,
                'subtotal_detail' => $cart->subtotal_detail,
            ]);

            // $currentStock = StokEtalase::where('id', $cart->stok_etalase_id)->first();
            // if ($currentStock->jumlah_stok == 0 || $currentStock->jumlah_stok < $cart->quantity) {
            //     return response()->json([
            //         'error' => "Stok tidak mencukupi"
            //     ], 400);
            // }
            // $currentStock->save();
            $total += $cart->subtotal_detail;
        }

        $detailPesanan = DB::table('detail_pesanan')->insert($listProduct);
        if (!$detailPesanan) {
            return response()->json([
                'error' => 'failed to create detail pesanan'
            ], 500);
        }
    }

    public function createTransaksi(Request $request, $id)
    {
        $this->createDetailPesananComplete($id);

        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'payment_option_id' => 'required',
            'status_pembayaran' => 'required',
            'subtotal_pengiriman' => 'required',
            'kode_company' => 'required',
            'nomor_pembayaran' => 'required'
        ], [
            'payment_option_id.required' => 'payment option tidak boleh kosong',
            'status_pembayaran.required' => 'Status Pembayaran tidak boleh kosong',
            'subtotal_pengiriman.required' => 'Subtotal Pengiriman tidak boleh kosong',
            'kode_company.required' => 'Kode Company tidak boleh kosong',
            'nomor_pembayaran.required' => 'Nomor pembayaran tidak boleh kosong',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 200);
        }

        $userId = Auth::user()->id;
        $cartItems = Cart::where('user_id', $userId)->get();
        $totalQuantity = 0;
        $totalSubtotal = 0;
        $totalDpp = 0;
        $totalPpn = 0;
        $dppTerutang = 0;
        $ppnTerutang = 0;
        $dppDibebaskan = 0;
        $ppnDibebaskan = 0;

        /// kalkulasi pajak, quantity dan subtotal
        foreach ($cartItems as $item) {
            $totalQuantity += $item->quantity;
            $totalSubtotal += $item->subtotal_detail;
            $totalDpp += $item->dpp;

            $pajakInfo = DB::table('stok_etalase')
                ->join('stok', 'stok.id', 'stok_etalase.stok_id')
                ->join('produk', 'produk.id', 'stok.produk_id')
                ->join('pajak', 'pajak.id', 'produk.pajak_id')
                ->select('pajak.jenis_pajak', 'pajak.persentase_pajak')
                ->where('stok_etalase.id', $item->stok_id)
                ->first();

            if (strtolower($pajakInfo->jenis_pajak) === 'include' || strtolower($pajakInfo->jenis_pajak) === 'exclude') {
                $totalPpn += $item->ppn;
                $dppTerutang += $item->dpp;
                $ppnTerutang += $item->ppn;
            } else {
                $dppDibebaskan += $item->dpp;
                $ppnDibebaskan += $item->ppn;
            }
        }

        $transaksi = new Transaksi;
        $transaksi->pesanan_id = $id;
        $transaksi->tipe_pembayaran = DB::table('payment_options')
            ->join('payment_types', 'payment_types.id', 'payment_options.payment_type_id')
            ->where('payment_options.id', $request->payment_option_id)
            ->value('type_name');
        $transaksi->status_pembayaran = $request->status_pembayaran;
        $transaksi->subtotal_produk = $totalSubtotal;
        $transaksi->subtotal_pengiriman = $request->subtotal_pengiriman;
        $transaksi->total_qty = $totalQuantity;
        $transaksi->total_pembayaran = $totalSubtotal + $request->subtotal_pengiriman;
        $transaksi->kode_transaksi = 'ORD/' . $id . '/' . now()->format('m') . '/' . now()->format('Y') . '/' . $request->kode_company;
        $transaksi->total_dpp = $totalDpp;
        $transaksi->total_ppn = $totalPpn;
        $transaksi->dpp_terutang = $dppTerutang;
        $transaksi->ppn_terutang = $ppnTerutang;
        $transaksi->dpp_dibebaskan = $dppDibebaskan;
        $transaksi->ppn_dibebaskan = $ppnDibebaskan;
        $transaksi->nomor_pembayaran = $request->nomor_pembayaran;
        $transaksi->payment_option_id = $request->payment_option_id;
        $transaksi->save();

        if (!$transaksi) {
            return response()->json([
                'error' => 'failed to create transaksi'
            ], 500);
        }

        return response()->json($transaksi, 200);
        // $this->addToPos($transaksi->id);
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
                'transaksi.is_paid',
                'pesanan.user_id',
                'pesanan.alamat_id',
                'pesanan.kurir_id',
                'pesanan.gudang_id',
                'pesanan.status_pemesanan',
                'pesanan.created_at as transaction_date',
                'pesanan.is_confirmed',
                'pesanan.is_delivered',
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
                'transaksi.is_paid',
                'pesanan.user_id',
                'pesanan.alamat_id',
                'pesanan.kurir_id',
                'pesanan.status_pemesanan',
                'pesanan.gudang_id',
                'pesanan.created_at as transaction_date',
                'pesanan.is_confirmed',
                'users.name',
                'transaksi.id as transaksi_id',
                'pesanan.id as pesanan_id',
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

        if (!$profileId) {
            return response()->json('user tidak memiliki akun PoS. tidak menambahkan inventory PoS', 200);
        }

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
