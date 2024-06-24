<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebugController extends Controller
{
    public function queryGet(Request $request)
    {
        $currentCart = Cart::where('user_id', Auth::user()->id)->where('stok_id', $request->stok_id)->first();
        $pajakInfo = DB::table('stok_etalase')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select('produk.nama_produk', 'pajak.jenis_pajak', 'pajak.persentase_pajak')
            ->where('stok_etalase.id', $request->stok_id)
            ->first();
        return response()->json($currentCart, $pajakInfo);
    }
}
