<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    // public function getUserCart(){
    //     $cart = DB::table('cart')
    //     ->join('')
    // }

    public function createUserCart(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => 'please insert data'
            ], 400);
        }
    }
}
