<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\Biodata;
use App\Models\DaftarAlamat;
use App\Models\PosCategory;
use App\Models\PosDiscount;
use App\Models\PosEmployee;
use App\Models\PosPayment;
use App\Models\PosProfile;
use App\Models\PosPromo;
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
        // Validate request data
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email|max:255|unique:users,email',
        //     'password' => 'required|string|min:8|confirmed',
        //     'no_hp' => 'required|string|max:28|unique:users,no_hp',
        //     'jalan' => 'required|string|max:255',
        //     'jalan_ext' => 'required|string|max:255',
        //     'nomor' => 'required|string|max:255',
        //     'rt' => 'required|string|max:255',
        //     'rw' => 'required|string|max:255',
        //     'provinsi_id' => 'required|integer|exists:provinsi,id',
        //     'kabupaten_id' => 'required|integer|exists:kabupaten,id',
        //     'kecamatan_id' => 'required|integer|exists:kecamatan,id',
        //     'kelurahan_id' => 'required|integer|exists:kelurahan,id',
        //     'negara' => 'required|string|max:255',
        //     'kode_pos' => 'required|string|max:10',
        //     'nama_rpk' => 'required|string|max:255',
        //     'no_ktp' => 'required|string|max:255',
        //     'kode_customer' => 'required|string|max:255',
        //     'ktp_img' => 'required|file|image|mimes:jpg,jpeg,png|max:10000',
        //     'npwp_img' => 'nullable|file|image|mimes:jpg,jpeg,png|max:10000',
        //     'nib_img' => 'nullable|file|image|mimes:jpg,jpeg,png|max:10000',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'error' => $validator->errors()->toJson()
        //     ], 400);
        // }

        $filePath = '';
        if ($request->hasFile('ktp_img')) {
            /* Alternate store filepath method
            $filePath = $request->file('ktp_img')->store('images/ktp', 'public');
            */
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

        $filePathNpwp = '';
        if ($request->hasFile('npwp_img')) {
            $url = env('API_DASHBOARD_URL') . '/mobile/receive-npwp-image';
            $image = $request->file('npwp_img');
            $fileName = 'image_' . time() . '.' . $image->getClientOriginalExtension();
            $imageContent = file_get_contents($image->getRealPath());
            $response = Http::attach(
                'npwp_img',
                $imageContent,
                $fileName
            )->post($url);
            $responseData = $response->json();
            $filePathNpwp = $responseData['path'];
        }

        $filePathNib = '';
        if ($request->hasFile('nib_img')) {
            $url = env('API_DASHBOARD_URL') . '/mobile/receive-nib-image';
            $image = $request->file('nib_img');
            $fileName = 'image_' . time() . '.' . $image->getClientOriginalExtension();
            $imageContent = file_get_contents($image->getRealPath());
            $response = Http::attach(
                'nib_img',
                $imageContent,
                $fileName
            )->post($url);
            $responseData = $response->json();
            $filePathNib = $responseData['path'];
        }

        $branchId = 213; // default jakarta
        $kodeCompany = "09001"; // default jakarta
        $companyId = 115; // default jakarta
        $wilayahKerja = DB::table('wilayah_kerja')->where('kabupaten_id', $request->kabupaten_id)->first();
        if ($wilayahKerja) {
            $companyBranch = DB::table('branches')
                ->join('companies', 'companies.id', 'branches.company_id')
                ->select('companies.id as company_id', 'companies.kode_company', 'branches.id as branch_id')
                ->where('company_id', $wilayahKerja->company_id)
                ->first();
            $branchId = $companyBranch->branch_id;
            $kodeCompany = $companyBranch->kode_company;
            $companyId = $companyBranch->company_id;
        }

        $user = User::create([
            'id' => DB::table('users')->max('id') + 1,
            'company_id' => $companyId,
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

        $this->generatePosProfile($user->id);

        $provinsi = DB::table('provinsi')->where('id', $request->provinsi_id)->value('display_name');
        $kabupaten = DB::table('kabupaten')->where('id', $request->kabupaten_id)->value('display_name');
        $kecamatan = DB::table('kecamatan')->where('id', $request->kecamatan_id)->value('display_name');
        $kelurahan = DB::table('kelurahan')->where('id', $request->kelurahan_id)->value('display_name');

        $alamat = Alamat::create([
            'jalan' => $request->jalan,
            'jalan_ext' => $request->jalan_ext,
            'blok' => $request->blok ? $request->blok : 'kosong',
            'nomor' => $request->nomor,
            'rt' => $request->rt,
            'rw' => $request->rw,
            'provinsi_id' => $request->provinsi_id,
            'provinsi' => $provinsi,
            'kabupaten_id' => $request->kabupaten_id,
            'kota_kabupaten' => $kabupaten,
            'kecamatan_id' => $request->kecamatan_id,
            'kecamatan' => $kecamatan,
            'kelurahan_id' => $request->kelurahan_id,
            'kelurahan' => $kelurahan,
            'negara' => $request->negara,
            'kode_pos' => $request->kode_pos,
        ]);

        if (!$alamat) {
            return response()->json([
                'error' => "failed to add new alamat"
            ], 200);
        };

        $biodata = new Biodata();
        $biodata->user_id = $user->id;
        $biodata->alamat_id = $alamat->id;
        $biodata->kode_customer = $request->kode_customer;
        $biodata->branch_id = $branchId;
        $biodata->kode_company = $kodeCompany;
        $biodata->nama_rpk = $request->nama_rpk;
        $biodata->no_ktp = $request->no_ktp;
        $biodata->ktp_img = $filePath;
        $biodata->npwp_img = $filePathNpwp;
        $biodata->nib_img = $filePathNib;
        $biodata->save();

        $daftarAlamat = new DaftarAlamat();
        $daftarAlamat->user_id = $user->id;
        $daftarAlamat->alamat_id = $alamat->id;
        $daftarAlamat->isActive = true;
        $daftarAlamat->save();

        if (!$biodata) {
            return response()->json([
                'error' => "failed to add new biodata"
            ], 200);
        };

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status_code' => 200,
            'data' => [$user, $alamat],
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function generatePosProfile($userId)
    {
        // $profile = PosProfile::firstOrNew(['user_id' => $userId]);
        // if (!$profile->exists) {
        $user = User::find($userId);
        $profile = new PosProfile();
        $profile->user_id = $userId;
        $profile->pos_name = $user->name;
        $profile->save();

        $category = new PosCategory();
        $category->profile_id = $profile->id;
        $category->category_name = "Lainnya";
        $category->category_desc = "Produk milik toko";
        $category->is_from_bulog = false;
        $category->save();

        $promo = new PosPromo();
        $promo->profile_id = $profile->id;
        $promo->promo_name = "Tidak Promo";
        $promo->promo_type = "Percent Off";
        $promo->promo_category = "Bulog Discount";
        $promo->promo_value = 0;
        $promo->is_active = true;
        $promo->is_from_bulog = true;
        $promo->promo_start = now();
        $promo->promo_end = now();
        $promo->save();

        $discount = new PosDiscount();
        $discount->profile_id = $profile->id;
        $discount->discount_name = "Tidak Diskon";
        $discount->discount_type = "Percent Off";
        $discount->discount_value = 0;
        $discount->is_active = true;
        $discount->is_from_bulog = true;
        $discount->save();

        $paymentMethod = new PosPayment();
        $paymentMethod->profile_id = $profile->id;
        $paymentMethod->payment_method = "Tunai";
        $paymentMethod->payment_info = "Pembayaran tunai";
        $paymentMethod->save();

        $employee = DB::table('pos_employees')->insert([
            'profile_id' => $profile->id,
            'pin' => bcrypt('123456'),
            'employee_name' => $user->name,
            'employee_phone' => $user->no_hp,
            'is_owner' => true,
        ]);
        // }
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
            ->join('provinsi', 'provinsi.id', 'alamat.provinsi_id')
            ->join('kabupaten', 'kabupaten.id', 'alamat.kabupaten_id')
            ->join('kecamatan', 'kecamatan.id', 'alamat.kecamatan_id')
            ->join('kelurahan', 'kelurahan.id', 'alamat.kelurahan_id')
            ->select(
                'users.name',
                'users.email',
                'users.no_hp',
                'users.role_id',
                'users.company_id',
                'biodata.user_id',
                'biodata.branch_id',
                'biodata.alamat_id',
                'biodata.kode_customer',
                'biodata.nama_rpk',
                'biodata.no_ktp',
                'biodata.ktp_img',
                'biodata.npwp_img',
                'biodata.nib_img',
                'alamat.jalan',
                'alamat.jalan_ext',
                'alamat.blok',
                'alamat.nomor',
                'alamat.rt',
                'alamat.rw',
                'provinsi.id as provinsi_id',
                'alamat.provinsi',
                'kabupaten.id as kabupaten_id',
                'alamat.kota_kabupaten',
                'kecamatan.id as kecamatan_id',
                'alamat.kecamatan',
                'kelurahan.id as kelurahan_id',
                'alamat.kelurahan',
                'alamat.negara',
                'alamat.kode_pos',
            )
            ->where('users.id', Auth::user()->id)
            ->first();
        $gudangId = DB::table('gudang')->where('branch_id', $customer->branch_id)->value('id');
        $customer->gudang_id = $gudangId;
        // $customer = DB::table('users')
        //     ->join('biodata', 'users.id', '=', 'biodata.user_id')
        //     ->join('alamat', 'biodata.alamat_id', '=', 'alamat.id')
        //     ->select(
        //         'users.name',
        //         'users.email',
        //         'users.no_hp',
        //         'users.role_id',
        //         'users.company_id',
        //         'biodata.user_id',
        //         'biodata.alamat_id',
        //         'biodata.kode_customer',
        //         'biodata.nama_rpk',
        //         'biodata.no_ktp',
        //         'biodata.ktp_img',
        //         'alamat.jalan',
        //         'alamat.jalan_ext',
        //         'alamat.blok',
        //         'alamat.nomor',
        //         'alamat.rt',
        //         'alamat.rw',
        //         'alamat.provinsi',
        //         'alamat.kota_kabupaten',
        //         'alamat.kecamatan',
        //         'alamat.negara',
        //         'alamat.kode_pos',
        //     )
        //     ->where('users.id', Auth::user()->id)
        //     ->first();

        return response()->json([
            'status_code' => 200,
            'data' => $customer,
        ]);
    }

    public function checkPhoneNumber(Request $request)
    {
        $checkNumber = User::where('no_hp', $request->no_hp)->first();
        if ($checkNumber) {
            return response()->json([
                'status' => 'Success',
                'message' => 'Nomor hp sudah digunakan',
                'isAvailable' => false,
            ]);
        } else {
            return response()->json([
                'status' => 'Success',
                'message' => 'Nomor hp tersedia',
                'isAvailable' => true,
            ]);
        }
        return response()->json([
            'status' => 'Error',
            'message' => 'query error',
        ]);
    }

    public function checkKtpNumber(Request $request)
    {
        $checkNumber = Biodata::where('no_ktp', $request->no_ktp)->first();
        if ($checkNumber) {
            return response()->json([
                'status' => 'Success',
                'message' => 'Nomor KTP sudah digunakan',
                'isAvailable' => false,
            ]);
        } else {
            return response()->json([
                'status' => 'Success',
                'message' => 'Nomor KTP tersedia',
                'isAvailable' => true,
            ]);
        }
        return response()->json([
            'status' => 'Error',
            'message' => 'query error',
        ]);
    }
}
