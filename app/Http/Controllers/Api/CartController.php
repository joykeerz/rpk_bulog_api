<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function getUserCart()
    {
        $cart = DB::table('carts')
            ->join('stok', 'carts.stok_id', 'stok.id')
            ->join('gudang', 'carts.gudang_id', 'gudang.id')
            ->join('produk', 'stok.produk_id', 'produk.id')
            ->join('pajak', 'produk.pajak_id', 'pajak.id')
            ->where('carts.user_id', '=', Auth::user()->id)
            ->select(
                'carts.id as cid',
                'stok.id as sid',
                'produk.id as pid',
                'gudang.id as gid',
                'produk.nama_produk as nama',
                'stok.harga_stok as harga',
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

        if (empty($cart) || $cart->count() < 1 || !$cart) {
            return response()->json([
                'data' => $cart
            ], 200);
        };

        return response()->json([
            'data' => $cart,
        ], 200);
    }

    public function createUserCart(Request $request)
    {
        // return request()->input();
        if (!$request->input()) {
            return response()->json([
                'error' => 'please insert data'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'stok_id' => 'required',
            'gudang_id' => 'required',
            'quantity' => 'required',
            'dpp' => 'required',
            'ppn' => 'required',
            'subtotal_detail' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson()
            ], 200);
        }

        $currentCart = Cart::where('user_id', Auth::user()->id)->where('stok_id', $request->stok_id)->first();
        if ($currentCart) {
            $currentCart->quantity += $request->quantity;
            $currentCart->dpp += $request->dpp;
            $currentCart->ppn += $request->ppn;
            $currentCart->subtotal_detail += $request->subtotal_detail;
            $currentCart->save();

            return response()->json([
                'data' => $currentCart
            ], 200);
        } else {
            $cart = new Cart;
            $cart->user_id = Auth::user()->id;
            $cart->stok_id = $request->stok_id;
            $cart->gudang_id = $request->gudang_id;
            $cart->quantity = $request->quantity;
            $cart->dpp = $request->dpp;
            $cart->ppn = $request->ppn;
            $cart->subtotal_detail = $request->subtotal_detail;
            $cart->save();
        }

        if (!$cart) {
            return response()->json([
                'error' => "failed to add new wishlist"
            ], 200);
        };

        return response()->json([
            'data' => $cart
        ], 200);
    }

    public function removeUserCart($id)
    {
        if (!$id) {
            return response()->json([
                'error' => 'please insert cart id'
            ], 400);
        }

        $cart = Cart::find($id);
        if (empty($cart) || !$cart) {
            return response()->json([
                'error' => "cart not found"
            ], '404');
        };

        $cart->delete();
        return response()->json([
            'message' => 'cart removed',
            'data' => $cart
        ], 200);
    }

    public function increaseUserCart($id)
    {
        if (!$id) {
            return response()->json([
                'error' => 'please insert cart id'
            ], 400);
        }

        $cart = Cart::find($id);
        $cart->quantity += 1;
        $cart->save();

        return response()->json([
            'message' => 'cart quantity increased',
            'quantity' => $cart->quantity,
        ], 200);
    }

    public function decrementUserCart($id)
    {
        if (!$id) {
            return response()->json([
                'error' => 'please insert cart id'
            ], 400);
        }

        $cart = Cart::find($id);
        if ($cart->quantity <= 0) {
            return response()->json([
                'message' => 'cart quantity cannot be decreased',
                'quantity' => $cart->quantity,
            ], 200);
        }
        $cart->quantity -= 1;
        $cart->save();

        return response()->json([
            'message' => 'cart quantity decreased',
            'quantity' => $cart->quantity,
        ], 200);
    }

    function clearCart()
    {
        $cart = Cart::where('user_id', Auth::user()->id)->get();

        if (empty($cart) || $cart->count() < 1 || !$cart) {
            return response()->json([
                'error' => "cart not found"
            ], '404');
        };

        $cart->delete();

        return response()->json([
            'message' => 'cart cleared',
        ], 200);
    }
}
