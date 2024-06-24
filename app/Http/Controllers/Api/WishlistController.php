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
    public function getUserWishlist($gudangId)
    {
        $userId = Auth::id();

        $wishlists = DB::table('wishlists')
            ->join('stok_etalase', 'stok_etalase.id', '=', 'wishlists.stok_id')
            ->join('stok', 'stok.id', '=', 'stok_etalase.stok_id')
            ->join('prices', function ($join) {
                $join->on('prices.company_id', '=', 'stok_etalase.company_id')
                    ->on('prices.produk_id', '=', 'stok_etalase.produk_id');
            })
            ->join('produk', 'produk.id', '=', 'stok_etalase.produk_id')
            ->join('gudang', 'gudang.id', '=', 'stok_etalase.gudang_id')
            ->join('pajak', 'produk.pajak_id', '=', 'pajak.id')
            ->where('wishlists.user_id', '=', $userId)
            ->where('wishlists.gudang_id', '=', $gudangId)
            ->select(
                'wishlists.id as wishlist_id',
                'stok.id as stok_id',
                'stok_etalase.id as stok_etalase_id',
                'produk.id as produk_id',
                'gudang.id as gudang_id',
                'produk.nama_produk as nama',
                'prices.price_value as harga',
                'produk.produk_file_path as image',
                'pajak.jenis_pajak',
                'pajak.persentase_pajak',
                'gudang.nama_gudang'
            )
            ->simplePaginate(15);

        return response()->json($wishlists, 200);
    }


    public function addUserWishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stok_etalase_id' => 'required',
            'gudang_id' => 'required',
        ], [
            'stok_etalase_id.required' => 'stok etalase id harus di isi',
            'gudang_id.required' => 'gudang id harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson()
            ], 200);
        }

        $userId = Auth::user()->id;
        $stokEtalaseId = $request->stok_etalase_id;
        $gudangId = $request->gudang_id;

        $currentWishlist = Wishlist::where('user_id', $userId)->where('stok_id', $stokEtalaseId)->first();
        if ($currentWishlist) {
            return response()->json([
                'message' => 'wishlist sudah ada'
            ], 200);
        }

        $wishlist = new Wishlist;
        $wishlist->user_id = $userId;
        $wishlist->stok_id = $stokEtalaseId;
        $wishlist->gudang_id = $gudangId;
        if ($request->has('wishlist_group')) {
            $wishlist->wishlist_group = $request->wishlist_group;
        }
        $wishlist->save();

        if (!$wishlist) {
            return response()->json([
                'error' => "failed to add new wishlist"
            ], 200);
        };

        return response()->json(true, 200);
    }

    public function removeUserWishlist($id)
    {
        $wishlist = Wishlist::find($id);

        if (!$wishlist) {
            return response()->json(['error' => 'Wishlist not found'], 404);
        }

        $wishlist->delete();

        return response()->json(false, 200);
    }

    public function toggleUserWishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stok_etalase_id' => 'required',
            'gudang_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson()
            ], 400);
        }

        $userId = Auth::id();
        $stokEtalaseId = $request->stok_etalase_id;
        $gudangId = $request->gudang_id;

        $wishlistItem = Wishlist::where('user_id', $userId)
            ->where('stok_id', $stokEtalaseId)
            ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            return response()->json(false, 200);
        } else {
            $wishlist = new Wishlist;
            $wishlist->user_id = $userId;
            $wishlist->stok_id = $stokEtalaseId;
            $wishlist->gudang_id = $gudangId;
            if ($request->has('wishlist_group')) {
                $wishlist->wishlist_group = $request->wishlist_group;
            }
            $wishlist->save();

            return response()->json(true, 200);
        }
    }
}
