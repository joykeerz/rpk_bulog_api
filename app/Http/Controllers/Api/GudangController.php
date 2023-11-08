<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class GudangController extends Controller
{
    public function getAllGudang(){
        $gudang  = DB::table('gudang')
        ->join('alamat', 'gudang.alamat_id', '=', 'alamat.id')
        ->select('gudang.*', 'alamat.*', 'gudang.id as gid', 'alamat.id as aid', 'gudang.created_at as cat')
        ->orderBy('cat', 'desc')
        ->get();

        if(empty($gudang)){
            return response()->json([
                'error' => "There's no data yet"
            ], '404');
        };

        return response()->json([
            'data' => $gudang,
        ], 200);
    }

    public function getGudang($id){
        $gudang = DB::table('gudang')
        ->join('alamat', 'gudang.alamat_id', '=', 'alamat.id')
        ->select('gudang.*', 'alamat.*', 'gudang.id as gid', 'alamat.id as aid')
        ->where('gudang.id', '=', $id)
        ->first();

        if(empty($gudang)){
            return response()->json([
                'error' => 'Gudang not found'
            ], '404');
        };

        return response()->json([
            'data' => $gudang,
        ], 200);
    }
}
