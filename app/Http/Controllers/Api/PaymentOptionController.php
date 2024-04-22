<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentOptionController extends Controller
{
    //pake id company
    public function getPaymentOptionById($id)
    {
        $paymentOptions = DB::table('payment_options')
            ->join('rekening_tujuan', 'rekening_tujuan.id', 'payment_options.rekening_tujuan_id')
            ->join('payment_terms', 'payment_terms.id', 'payment_options.payment_term_id')
            ->select(
                'payment_options.id',
                'rekening_tujuan.name as rekening_name',
                'rekening_tujuan.bank_acc_number',
                'payment_terms.name as term_name',
                'payment_options.payment_type'
            )
            ->where('payment_options.company_id', $id)
            ->get();

        if (!$paymentOptions) {
            return response()->json([
                'error' => 'No payment option in company'
            ], '404');
        };

        return response()->json($paymentOptions, 200);
    }

    public function getPaymentOptionByAuth()
    {
        $comapnyId = Biodata::where('user_id', Auth::user()->id)->select('company_id')->first();
        $paymentOptions = DB::table('payment_options')
            ->join('rekening_tujuan', 'rekening_tujuan.id', 'payment_options.rekening_tujuan_id')
            ->join('payment_terms', 'payment_terms.id', 'payment_options.payment_term_id')
            ->select(
                'payment_options.id',
                'rekening_tujuan.name as rekening_name',
                'rekening_tujuan.bank_acc_number',
                'payment_terms.name as term_name',
                'payment_options.payment_type'
            )
            ->where('payment_options.company_id', $comapnyId)
            ->get();

        if (!$paymentOptions) {
            return response()->json([
                'error' => 'No payment option in company'
            ], '404');
        };

        return response()->json($paymentOptions,200);
    }
}
