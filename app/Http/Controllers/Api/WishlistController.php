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
        if (!Auth::user()) {
            return response()->json([
                'error' => 'User need to login first'
            ], 400);
        }

        $wishlist = DB::table('wishlists')
            ->where('user_id', '=', Auth::user()->id)
            ->get();

        if (empty($wishlist) || $wishlist->count() < 1 || !$wishlist) {
            return response()->json([
                'error' => "there's no wishlist yet"
            ], '404');
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
        ], [
            'stok_id.required' => 'stok harus di isi',
        ]);

        $wishlist = new Wishlist;
        $wishlist->user_id = Auth::user()->id;
        $wishlist->stok_id = $request->stok_id;
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
