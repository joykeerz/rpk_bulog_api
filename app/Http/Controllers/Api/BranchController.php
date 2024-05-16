<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function setBranchCompany(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'company_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 200);
        }

        $biodata = Biodata::where('user_id', Auth::user()->id)->first();
        $biodata->branch_id = $request->branch_id;
        $biodata->save();

        $user = User::find(Auth::user()->id);
        $user->company_id = $request->company_id;
        $user->save();

        return response()->json('branch dan company berhasil diubah', 200);
    }

    public function getBranchCompany()
    {
        $branchCompany = DB::table('users')
            ->join('biodata', 'biodata.user_id', 'user.id')
            ->select('users.company_id', 'biodata.branch_id')
            ->where('users.id', Auth::user()->id)
            ->first();

        if (!$branchCompany) {
            return response()->json("data gagal diambil", 500);
        }

        return response()->json($branchCompany, 200);
    }
}
