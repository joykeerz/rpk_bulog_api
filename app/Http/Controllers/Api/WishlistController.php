<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class WishlistController extends Controller
{
    public function getUserWishlist()
    {
        $wishlist = DB::table('wishlists')
            ->join('stok', 'wishlists.stok_id', 'stok.id')
            ->join('gudang', 'wishlists.gudang_id', 'gudang.id')
            ->join('produk', 'stok.produk_id', 'produk.id')
            ->join('pajak', 'produk.pajak_id', 'pajak.id')
            ->where('wishlists.user_id', '=', Auth::user()->id)
            ->select(
                'wishlists.id as wid',
                'stok.id as sid',
                'produk.id as pid',
                'gudang.id as gid',
                'produk.nama_produk as nama',
                'stok.harga_stok as harga',
                'produk.produk_file_path as image',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang'
            )
            ->get();

        if (empty($wishlist) || $wishlist->count() < 1 || !$wishlist) {
            return response()->json([
                'data' => $wishlist
            ], 200);
        };

        return response()->json([
            'data' => $wishlist,
        ], 200);
    }

    public function addUserWishlist(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'stok_id' => 'required',
            'gudang_id' => 'required',
        ], [
            'stok_id.required' => 'stok harus di isi',
            'gudang_id.required' => 'gudang harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson()
            ], 200);
        }

        $currentWishlist = Wishlist::where('user_id', Auth::user()->id)->where('stok_id', $request->stok_id)->first();
        if ($currentWishlist) {
            return response()->json([
                'message' => 'wishlist sudah ada'
            ], 200);
        }

        $wishlist = new Wishlist;
        $wishlist->user_id = Auth::user()->id;
        $wishlist->stok_id = $request->stok_id;
        $wishlist->gudang_id = $request->gudang_id;
        if ($request->has('wishlist_group')) {
            $wishlist->wishlist_group = $request->wishlist_group;
        }
        $wishlist->save();

        if (!$wishlist) {
            return response()->json([
                'error' => "failed to add new wishlist"
            ], 200);
        };

        return response()->json([
            'data' => $wishlist
        ], 200);
    }

    public function removeUserWishlist($id)
    {
        if (!$id) {
            return response()->json([
                'error' => 'please insert wishlist id'
            ], 400);
        }

        $wishlist = Wishlist::find($id);
        if (empty($wishlist) || $wishlist->count() < 1 || !$wishlist) {
            return response()->json([
                'error' => "wishlist not found"
            ], '404');
        };

        $wishlist->delete();
        return response()->json([
            'message' => 'wishlist removed',
            'data' => $wishlist
        ], 200);
    }
}
