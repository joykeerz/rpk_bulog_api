<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\Biodata;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'jalan_ext' => 'required|string|max:255',
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
            'kode_customer' => 'required|string|max:255',
            'ktp_img' => 'required|file|image|mimes:jpg,jpeg,png|max:10000',
        ], [
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password harus diisi',
            'no_hp.required' => 'No HP harus diisi',
            'no_hp.unique' => 'No HP sudah terdaftar',
            'jalan.required' => 'Jalan harus diisi',
            'jalan_ext.required' => 'Jalan Ext harus diisi',
            'blok.required' => 'Blok harus diisi',
            'rt.required' => 'RT harus diisi',
            'rw.required' => 'RW harus diisi',
            'provinsi.required' => 'Provinsi harus diisi',
            'kota_kabupaten.required' => 'Kota/Kabupaten harus diisi',
            'kecamatan.required' => 'Kecamatan harus diisi',
            'kelurahan.required' => 'Kelurahan harus diisi',
            'negara.required' => 'Negara harus diisi',
            'kode_pos.required' => 'Kode Pos harus diisi',
            'nama_rpk.required' => 'Nama RPK harus diisi',
            'no_ktp.required' => 'No KTP harus diisi',
            'kode_customer.required' => 'Kode Customer harus diisi',
            'ktp_img.required' => 'KTP harus diisi',
            'ktp_img.image' => 'KTP harus berupa gambar',
            'ktp_img.file' => 'KTP harus berupa file',
            'ktp_img.mimes' => 'KTP harus berformat jpg, jpeg atau png',
            'ktp_img.max' => 'KTP maksimal 10MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson()
            ], 200);
        }

        $filePath = 'none';
        if ($request->hasFile('ktp_img')) {
            $filePath = $request->file('ktp_img')->store('images/ktp', 'public');
            $validatedData['ktp_img'] = $filePath;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_hp' => $request->no_hp,
            'role_id' => 5,
        ]);

        if (!$user) {
            return response()->json([
                'error' => "failed to add new user"
            ], 200);
        };

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
            'negara' => $request->negara,
            'kode_pos' => $request->kode_pos,
        ]);

        if (!$alamat) {
            return response()->json([
                'error' => "failed to add new alamat"
            ], 200);
        };

        $biodata =  Biodata::create([
            'user_id' => $user->id,
            'alamat_id' => $alamat->id,
            'nama_rpk' => $request->nama_rpk,
            'no_ktp' => $request->no_ktp,
            'kode_customer' => $request->kode_customer,
            'ktp_img' => $filePath,
        ]);

        if (!$biodata) {
            return response()->json([
                'error' => "failed to add new biodata"
            ], 200);
        };

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [$user, $alamat],
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
            ], 200);
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
        $customer = DB::table('users')
            ->join('biodata', 'users.id', '=', 'biodata.user_id')
            ->join('alamat', 'biodata.alamat_id', '=', 'alamat.id')
            ->select(
                'users.name',
                'users.email',
                'users.no_hp',
                'users.role_id',
                'biodata.user_id',
                'biodata.alamat_id',
                'biodata.kode_customer',
                'biodata.nama_rpk',
                'biodata.no_ktp',
                'biodata.ktp_img',
                'alamat.jalan',
                'alamat.jalan_ext',
                'alamat.blok',
                'alamat.rt',
                'alamat.rw',
                'alamat.provinsi',
                'alamat.kota_kabupaten',
                'alamat.kecamatan',
                'alamat.kelurahan',
                'alamat.negara',
                'alamat.kode_pos',
            )
            ->where('users.id', Auth::user()->id)
            ->first();
        return response()->json([
            'status_code' => 200,
            'data' => $customer,
        ]);
    }
}
