<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class GudangController extends Controller
{
    public function getAllGudang()
    {
        $gudang  = DB::table('gudang')
            ->join('alamat', 'gudang.alamat_id', '=', 'alamat.id')
            ->select('gudang.*', 'alamat.*', 'gudang.id as gid', 'alamat.id as aid', 'gudang.created_at as cat')
            ->orderBy('cat', 'desc')
            ->simplePaginate(10);

        if (empty($gudang)) {
            return response()->json([
                'error' => "There's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $gudang,
        ], 200);
    }

    public function getGudang($id)
    {
        $gudang = DB::table('gudang')
            ->join('alamat', 'gudang.alamat_id', '=', 'alamat.id')
            ->select('gudang.*', 'alamat.*', 'gudang.id as gid', 'alamat.id as aid')
            ->where('gudang.id', '=', $id)
            ->first();

        if (!$gudang) {
            return response()->json([
                'error' => 'Gudang not found'
            ], '404');
        };

        return response()->json([
            'data' => $gudang,
        ], 200);
    }

    public function getgudangByUser()
    {
        $customer = DB::table('users')
            ->join('biodata', 'biodata.user_id', 'users.id')
            ->where('users.id', Auth::user()->id)
            ->first();

        $gudang = DB::table('gudang')
            ->join('alamat', 'alamat.id', 'gudang.alamat_id')
            ->where('gudang.branch_id', $customer->branch_id)
            ->select('gudang.nama_gudang', 'gudang.id')
            ->first();

        if (!$gudang) {
            return response()->json([
                'error' => 'user is not registered in any branch'
            ], '404');
        };

        return response()->json([
            'data' => $gudang,
        ], 200);
    }

    public function GetKodeCompanyByGudang(string $id)
    {
        $kodeCompany = DB::table('gudang')
            ->join('companies', 'gudang.company_id', '=', 'companies.id')
            ->select('companies.kode_company')
            ->where('gudang.id', '=', $id)
            ->first();

        if (empty($kodeCompany)) {
            return response()->json([
                'error' => 'Kode Company not found'
            ], '404');
        };

        return response()->json([
            'data' => $kodeCompany,
        ], 200);
    }

    public function searchGudang(Request $request)
    {
        $gudang = DB::table('gudang')
            ->where('gudang.nama_gudang', 'ilike', '%' . $request->nama_gudang . '%')
            ->limit(10)
            ->get();

        if (empty($gudang)) {
            return response()->json([
                'error' => 'gudang not found'
            ], '404');
        };

        return response()->json($gudang, 200);
    }
}
