<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\Biodata;
use Illuminate\Http\Request;
use App\Models\DaftarAlamat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DaftarAlamatController extends Controller
{
    public function getDaftarAlamatUser()
    {
        $daftarAlamat = DaftarAlamat::with(['alamat'])->where('user_id', Auth::user()->id)->get();
        return response()->json($daftarAlamat, 200);
    }

    public function addAlamat(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'jalan' => 'required',
            'provinsi' => 'required',
            'kota_kabupaten' => 'required',
            'kecamatan' => 'required',
            'kode_pos' => 'required',
        ], [
            'jalan.required' => 'jalan tidak boleh kosong',
            'provinsi.required' => 'provinsi tidak boleh kosong',
            'kota_kabupaten.required' => 'kota tidak boleh kosong',
            'kecamatan.required' => 'kecamatan tidak boleh kosong',
            'kode_pos.required' => 'kode pos tidak boleh kosong',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson()
            ], 200);
        }

        $alamat = Alamat::create([
            'jalan' => $request->jalan,
            'jalan_ext' => $request->jalan_ext,
            'blok' => $request->blok,
            'rt' => $request->rt,
            'rw' => $request->rw,
            'provinsi' => $request->provinsi,
            'kota_kabupaten' => $request->kota_kabupaten,
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
            'negara' => 'Indonesia',
            'kode_pos' => $request->kode_pos,
        ]);

        if (!$alamat) {
            return response()->json([
                'error' => "failed to add new alamat"
            ], 200);
        };

        $daftarAlamat = DaftarAlamat::create([
            'user_id' => Auth::user()->id,
            'alamat_id' => $alamat->id,
            'isActive' => false,
        ]);

        if (!$daftarAlamat) {
            return response()->json([
                'error' => "failed to add to daftar alamat"
            ], 200);
        };
        return response()->json([
            'data' => $alamat
        ], 200);
    }

    public function removeAlamat($alamatID)
    {
        $alamat = Alamat::find($alamatID);
        $daftarAlamat = DaftarAlamat::where('alamat_id', $alamatID)->first();
        $checkAlamat = DaftarAlamat::where('user_id', $daftarAlamat->user_id)->get();

        if ($checkAlamat->count() <= 1) {
            return response()->json([
                'error' => 'alamat hanya satu, tidak bisa dihapus'
            ], 200);
        }

        if ($daftarAlamat->isActive == true) {
            return response()->json([
                'error' => 'Alamat aktif tidak bisa dihapus'
            ], 200);
        }

        $alamat->delete();
        $daftarAlamat->delete();
        return response()->json([
            'message' => 'alamat berhasil dihapus'
        ], 200);
    }

    public function toggleAlamat($alamatID)
    {
        $daftarAlamat = DaftarAlamat::where('alamat_id', $alamatID)->first();
        $otherAlamat = DaftarAlamat::where('user_id', $daftarAlamat->user_id)->update(['isActive' => false]);

        $daftarAlamat->isActive = true;
        $daftarAlamat->save();

        $biodata = Biodata::where('user_id', $daftarAlamat->user_id)->first();
        $biodata->alamat_id = $alamatID;
        $biodata->save();

        return response()->json([
            'message' => 'alamat berhasil diaktifkan',
            'alamat_id' => $biodata->alamat_id,
            'alamat' => $daftarAlamat
        ], 200);
    }
}
