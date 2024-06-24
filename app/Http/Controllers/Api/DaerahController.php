<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DaerahController extends Controller
{
    public function getAllProvinsi()
    {
        $provinsi = DB::table('provinsi')->get();
        if (empty($provinsi)) {
            return response()->json('data provinsi kosong', 404);
        }
        return response()->json($provinsi, 200);
    }

    public function getAllKota()
    {
        $kota = DB::table('kota')->get();
        if (empty($kota)) {
            return response()->json('data kota kosong', 404);
        }
        return response()->json($kota, 200);
    }

    public function getKotaByProvinsiId($provinsiId)
    {
        $kota = DB::table('kota')->where('provinsi_id', $provinsiId)->get();
        if (empty($kota)) {
            return response()->json('data kota tidak ditemukan', 404);
        }
        return response()->json($kota, 200);
    }

    public function getAllKabupaten()
    {
        $kota = DB::table('kabupaten')->get();
        if (empty($kota)) {
            return response()->json('data kota kosong', 404);
        }
        return response()->json($kota, 200);
    }

    public function getKabupatenByProvinsiId($provinsiId)
    {
        $kota = DB::table('kabupaten')->where('provinsi_id', $provinsiId)->get();
        if (empty($kota)) {
            return response()->json('data kota tidak ditemukan', 404);
        }
        return response()->json($kota, 200);
    }

    public function getAllKecamatan()
    {
        $kecamatan = DB::table('kecamatan')->get();
        if (empty($kecamatan)) {
            return response()->json('data kecamatan kosong', 404);
        }
        return response()->json($kecamatan, 200);
    }

    public function getKecamatanByKotaId($kotaId)
    {
        $kecamatan = DB::table('kecamatan')->where('kabupaten_id', $kotaId)->get();
        if (empty($kecamatan)) {
            return response()->json('data kecamatan tidak ditemukan', 404);
        }
        return response()->json($kecamatan, 200);
    }

    public function getKecamatanByKabupatenId($kabupatenId)
    {
        $kecamatan = DB::table('kecamatan')->where('kabupaten_id', $kabupatenId)->get();
        if (empty($kecamatan)) {
            return response()->json('data kecamatan tidak ditemukan', 404);
        }
        return response()->json($kecamatan, 200);
    }

    public function getAllKelurahan()
    {
        $kelurahan = DB::table('kelurahan')->get();
        if (empty($kelurahan)) {
            return response()->json('data kelurahan kosong', 404);
        }
        return response()->json($kelurahan, 200);
    }

    public function getKelurahanByKecamatanId($kecamatanId)
    {
        $kelurahan = DB::table('kelurahan')->where('kecamatan_id', $kecamatanId)->get();
        if (empty($kelurahan)) {
            return response()->json('data kelurahan tidak ditemukan', 404);
        }
        return response()->json($kelurahan, 200);
    }
}
