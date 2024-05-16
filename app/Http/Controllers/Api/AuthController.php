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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Obuchmann\OdooJsonRpc\Odoo;

class AuthController extends Controller
{
    public function register(Request $request, Odoo $odoo)
    {
        /* DEBUG MODE
        $debug1 = DB::table('biodata')->max('id') + 1;
        $debug2 = DB::table('users')->max('id') + 1;
        return response()->json([$debug1, $debug2], 200);
        */

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
            /// Alternate store filepath method
            // $filePath = $request->file('ktp_img')->store('images/ktp', 'public');
            $url = env('API_DASHBOARD_URL') . '/mobile/receive-ktp-image';
            $image = $request->file('ktp_img');
            $fileName = 'image_' . time() . '.' . $image->getClientOriginalExtension();
            $imageContent = file_get_contents($image->getRealPath());
            $response = Http::attach(
                'ktp_img',
                $imageContent,
                $fileName
            )->post($url);
            $responseData = $response->json();
            $filePath = $responseData['path'];
        }

        $user = User::create([
            'id' => DB::table('users')->max('id') + 1,
            'company_id' => 115,
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

        // $biodata =  Biodata::create([
        //     'user_id' => $user->id,
        //     'alamat_id' => $alamat->id,
        //     'kode_customer' => $request->kode_customer,
        //     'branch_id' => 213,
        //     'company_id' => 115,
        //     'nama_rpk' => $request->nama_rpk,
        //     'no_ktp' => $request->no_ktp,
        //     'ktp_img' => $filePath,
        // ]);

        $biodata = new Biodata();
        $biodata->user_id = $user->id;
        $biodata->alamat_id = $alamat->id;
        $biodata->kode_customer = $request->kode_customer;
        $biodata->branch_id = 213;
        $biodata->kode_company = "09001";
        $biodata->nama_rpk = $request->nama_rpk;
        $biodata->no_ktp = $request->no_ktp;
        $biodata->ktp_img = $filePath;
        $biodata->save();

        if (!$biodata) {
            return response()->json([
                'error' => "failed to add new biodata"
            ], 200);
        };

        $token = $user->createToken('auth_token')->plainTextToken;

        // $this->addToErp($user, $biodata, $alamat, $odoo);

        return response()->json([
            'status_code' => 200,
            'data' => [$user, $alamat],
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
            'no_hp' => 'required|numeric',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        if (!Auth::attempt($request->only('no_hp', 'password'))) {
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

        $user = User::where('no_hp', $request->no_hp)->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status_code' => 200,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'status_verifikasi' => $user->isVerified,
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
                'users.company_id',
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

    public function getDaerah()
    {
    }

    public function addToErp($user, $biodata, $alamat, Odoo $odoo)
    {
        Log::info("adding to erp");

        $resPartner = $odoo->create('res.partner', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->no_hp,
            'mobile' => $user->no_hp,
            'login_user' => $user->no_hp,
            'nama_id_rpk' => $biodata->nama_rpk,
            'ktp' => $biodata->no_ktp,
            'cabang_terdaftar' => $biodata->branch_id,
            'jenis_partner' => 2,
            'street' => $alamat->jalan,
            'street2' => $alamat->jalan_ext,
            'blok' => $alamat->blok,
            'rt' => $alamat->rt,
            'rw' => $alamat->rw,
            'zip' => $alamat->kode_pos,
            'country_id' => 100,
            'is_rpk_partner' => true,
            'default_warehouse_id' => 1804,
            'warehouse_company_id' => 115,
        ]);

        if (!$resPartner) {
            return response()->json('failed to insert in erp', 400);
        }
        Log::info("done adding to erp");

        Log::info("updating current user id with erp");
        $user->id = $resPartner;
        $user->save();
        $biodata->user_id = $user->id;
        $biodata->save();
        Log::info("updating done");
    }
}
