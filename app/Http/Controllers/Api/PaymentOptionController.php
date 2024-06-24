<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentOptionController extends Controller
{
    //pake id company
    public function getPaymentOptionById($id)
    {
        $paymentOptions = DB::table('payment_options')
            ->join('rekening_tujuan', 'rekening_tujuan.id', 'payment_options.rekening_tujuan_id')
            ->join('payment_terms', 'payment_terms.id', 'payment_options.payment_term_id')
            ->join('payment_types', 'payment_types.id', 'payment_options.payment_type_id')
            ->select(
                'payment_options.id',
                'rekening_tujuan.name as rekening_name',
                'rekening_tujuan.bank_acc_number',
                'payment_terms.name as term_name',
                'payment_types.display_name'
            )
            ->where('payment_options.id', $id)
            ->get();

        if (!$paymentOptions) {
            return response()->json([
                'error' => 'No payment option in company'
            ], '404');
        };

        return response()->json($paymentOptions, 200);
    }

    public function getPaymentOptionByUser()
    {
        $companyId = User::where('id', Auth::user()->id)->value('company_id');
        $paymentOptions = DB::table('payment_options')
            ->join('rekening_tujuan', 'rekening_tujuan.id', 'payment_options.rekening_tujuan_id')
            ->join('payment_terms', 'payment_terms.id', 'payment_options.payment_term_id')
            ->join('payment_types', 'payment_types.id', 'payment_options.payment_type_id')
            ->select(
                'payment_options.id',
                'rekening_tujuan.name as rekening_name',
                'rekening_tujuan.bank_acc_number',
                'payment_terms.name as term_name',
                'payment_types.display_name'
            )
            ->where('payment_options.company_id', $companyId)
            ->get();

        if (!$paymentOptions) {
            return response()->json([
                'error' => 'No payment option in company'
            ], '404');
        };

        return response()->json($paymentOptions, 200);
    }

    public function getPaymentDetail($id)
    {
        $payment = DB::table('transaksi')
            ->join('payment_options', 'payment_options.id', 'transaksi.payment_option_id')
            ->join('rekening_tujuan', 'rekening_tujuan.id', 'payment_options.rekening_tujuan_id')
            ->join('payment_terms', 'payment_terms.id', 'payment_options.payment_term_id')
            ->join('payment_types', 'payment_types.id', 'payment_options.payment_type_id')
            ->select(
                'payment_options.id as payment_option_id',
                'rekening_tujuan.name as rekening_name',
                'rekening_tujuan.bank_acc_number',
                'payment_terms.name as term_name',
                'payment_types.display_name',
                'transaksi.status_pembayaran',
                'transaksi.total_pembayaran',
                'transaksi.created_at'
            )
            ->where('transaksi.id', $id)
            ->first();

        if (!$payment) {
            return response()->json([
                'error' => 'Payment info not found'
            ], '404');
        }

        // Add 2 hours to the created_at timestamp
        $payment_deadline = Carbon::parse($payment->created_at)->addHours(2)->toDateTimeString();

        // Add payment_deadline to the $payment object
        $payment->payment_deadline = $payment_deadline;

        return response()->json($payment, 200);
    }

    public function changeStatusPembayaran($id)
    {
        $transaksi = Transaksi::find($id);
        $transaksi->status_pembayaran = 'sudah dibayar';
        $transaksi->is_paid = true;
        $transaksi->save();

        if (!$transaksi) {
            return response()->json([
                'error' => 'gagal mengubah'
            ], '404');
        };

        return response()->json('berhasil dibayar', 200);
    }
}
