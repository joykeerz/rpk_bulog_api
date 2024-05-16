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
            ->join('stok_etalase', 'stok_etalase.id', 'carts.stok_id')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('prices', 'prices.id', 'stok.id')
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
            ->simplePaginate(15);

        if (empty($cart)) {
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
        if (!$request->input()) {
            return response()->json([
                'error' => 'please insert data'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'stok_id' => 'required',  //ini stok etalase id
            'gudang_id' => 'required',
            'quantity' => 'required',
            // 'dpp' => 'required',
            // 'ppn' => 'required',
            // 'subtotal_detail' => 'required',
            'harga' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson()
            ], 200);
        }

        $currentCart = Cart::where('user_id', Auth::user()->id)->where('stok_id', $request->stok_id)->first();
        $pajakInfo = DB::table('stok_etalase')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('produk', 'produk.id', 'stok.produk_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select('pajak.jenis_pajak', 'pajak.persentase_pajak')
            ->where('stok_etalase.id', $request->stok_id)
            ->first();
        $dpp = 0;
        $ppn = 0;
        $subtotal = $request->quantity * $request->harga;

        if (strtolower($pajakInfo->jenis_pajak) === 'include') {
            $dpp = $subtotal * (100 / (100 + $pajakInfo->persentase_pajak));
            $ppn = $dpp * ($pajakInfo->persentase_pajak / 100);
            $subtotal = $dpp + $ppn;
        } else if (strtolower($pajakInfo->jenis_pajak) === 'exclude') {
            $dpp = $subtotal;
            $ppn = $dpp * ($pajakInfo->persentase_pajak / 100);
            $subtotal = $dpp + $ppn;
        } else if (strtolower($pajakInfo->jenis_pajak) === 'dibebaskan') {
            $dpp = $subtotal;
            $ppn = $subtotal * ($pajakInfo->persentase_pajak / 100);
            $subtotal = $dpp;
        }

        if ($currentCart) {
            $currentCart->quantity += $request->quantity;
            $currentCart->dpp += $dpp;
            $currentCart->ppn += $ppn;
            $currentCart->subtotal_detail += $subtotal;
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
            $cart->dpp = $dpp;
            $cart->ppn = $ppn;
            $cart->subtotal_detail = $subtotal;
            $cart->save();
        }

        if (!$cart) {
            return response()->json([
                'error' => "failed to add new cart"
            ], 200);
        };

        return response()->json([
            'data' => $cart
        ], 200);
    }

    public function updateUserCart(Request $request, $id)
    {

        if (!$request->input()) {
            return response()->json([
                'error' => 'please insert data'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required',
            'harga' => 'required'
            // 'dpp' => 'required',
            // 'ppn' => 'required',
            // 'subtotal_detail' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson()
            ], 200);
        }

        $cart = Cart::find($id);
        if (!$cart) {
            return response()->json([
                'error' => 'cart not found'
            ], 404);
        }

        $dpp = 0;
        $ppn = 0;
        $pajakInfo = DB::table('stok_etalase')
            ->join('stok', 'stok.id', 'stok_etalase.stok_id')
            ->join('produk', 'produk.id', 'stok.produk_id')
            ->join('pajak', 'pajak.id', 'produk.pajak_id')
            ->select('pajak.jenis_pajak', 'pajak.persentase_pajak')
            ->where('stok_etalase.id', $cart->stok_id)
            ->first();
        $subtotal = $request->quantity * $request->harga;

        if (strtolower($pajakInfo->jenis_pajak) === 'include') {
            $dpp = $subtotal * (100 / (100 + $pajakInfo->persentase_pajak));
            $ppn = $dpp * ($pajakInfo->persentase_pajak / 100);
            $subtotal = $dpp + $ppn;
        } else if (strtolower($pajakInfo->jenis_pajak) === 'exclude') {
            $dpp = $subtotal;
            $ppn = $dpp * ($pajakInfo->persentase_pajak / 100);
            $subtotal = $dpp + $ppn;
        } else if (strtolower($pajakInfo->jenis_pajak) === 'dibebaskan') {
            $dpp = $subtotal;
            $ppn = $subtotal * ($pajakInfo->persentase_pajak / 100);
            $subtotal = $dpp;
        }

        $cart->quantity = $request->quantity;
        $cart->dpp = $dpp;
        $cart->ppn = $ppn;
        $cart->subtotal_detail = $subtotal;
        $cart->save();

        return response()->json('cart updated', 200);
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

    public function removeAllUserCart()
    {
        $cart = Cart::where('user_id', Auth::user()->id);
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

    function countCartQuantity()
    {
        $userId = Auth::user()->id;
        $cartItems = Cart::where('user_id', $userId)->get();
        $totalQuantity = 0;
        $totalSubtotal = 0;

        foreach ($cartItems as $item) {
            $totalQuantity += $item->quantity;
            $totalSubtotal += $item->subtotal_detail;
        }

        return response()->json([
            'total_quantity_cart' => $totalQuantity,
            'total_subtotal' => $totalSubtotal
        ], 200);
    }
}
