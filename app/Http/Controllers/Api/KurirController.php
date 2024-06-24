<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Models\Kurir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KurirController extends Controller
{
    //
    public function getAllKurir()
    {
        $kurir = Kurir::all();
        if ($kurir->isEmpty()) {
            return response()->json('no kurir data', 404);
        }

        return response()->json($kurir, 200);
    }

    public function getKurirByUser()
    {
        $userCompanyId = DB::table('biodata')
            ->join('branches', 'branches.id', '=', 'biodata.branch_id')
            ->where('user_id', Auth::user()->id)
            ->value('company_id');

        $kurir = Kurir::where('company_id', $userCompanyId)->get();

        if ($kurir->isEmpty()) {
            return response()->json('kurir in this kanwil not found', 404);
        }

        return response()->json($kurir, 200);
    }


    public function getKurirById($id)
    {
        $kurir = Kurir::find($id);

        if ($kurir === null) {
            return response()->json('kurir not found', 404);
        }

        return response()->json($kurir, 200);
    }
}
