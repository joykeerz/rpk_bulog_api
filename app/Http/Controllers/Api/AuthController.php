<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\Biodata;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'no_hp' => 'required|string|max:15|unique:users,no_hp',
            'jalan' => 'required|string|max:255',
            'jalant_ext' => 'required|string|max:255',
            'blok' => 'required|string|max:255',
            'rt' => 'required|string|max:255',
            'rw' => 'required|string|max:255',
            'provinsi' => 'required|string|max:255',
            'kota_kabupaten' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
            'negara' => 'required|string|max:255',
            'kode_pos' => 'required|string|max:255',
            'nama_rpk' => 'required|string|max:255',
            'no_ktp' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_hp' => $request->no_hp,
        ]);

        if (!$user) {
            return response()->json([
                'error' => "failed to add new user"
            ], 400);
        };

        $alamat = Alamat::create([
            'jalan' => $request->jalan,
            'jalan_ext' => $request->jalant_ext,
            'blok' => $request->blok,
            'rt' => $request->rt,
            'rw' => $request->rw,
            'provinsi' => $request->provinsi,
            'kota_kabupaten' => $request->kota_kabupaten,
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
            'negara' => $request->negara,
            'kode_pos' => $request->kode_pos,
        ]);

        if (!$alamat) {
            return response()->json([
                'error' => "failed to add new alamat"
            ], 400);
        };

        $biodata =  Biodata::create([
            'user_id' => $user->id,
            'alamat_id' => $alamat->id,
            'nama_rpk' => $request->nama_rpk,
            'no_ktp' => $request->no_ktp,
        ]);

        if (!$biodata) {
            return response()->json([
                'error' => "failed to add new biodata"
            ], 400);
        };

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [$user,$alamat,],
            'status_code' => 200,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function login(Request $request)
    {

        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Unauthorized'
            ]);
        }

        if (!Auth::attempt($validator->validated())) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Unauthorized'
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status_code' => 200,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json([
            'status_code' => 200,
            'message' => 'Tokens Revoked'
        ]);
    }

    public function getCurrentUser()
    {
        return response()->json([
            'status_code' => 200,
            'user' => Auth::user()
        ]);
    }
}
