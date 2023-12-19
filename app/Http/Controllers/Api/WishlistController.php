<?php

namespace App\Http\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    //
    public function getUserWishlist()
    {
        $wishlist = DB::table('wishlists')->where('user_id', '=', Auth::user()->id)->get();

        if (empty($wishlist) || $wishlist->count() < 1) {
            return response()->json([
                'error' => "there's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $wishlist,
        ], 200);
    }
}
