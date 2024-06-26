<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\BeritaController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\GudangController;
use App\Http\Controllers\Api\PesananController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\DaerahController;
use App\Http\Controllers\Api\DaftarAlamatController;
use App\Http\Controllers\Api\DebugController;
use App\Http\Controllers\Api\KurirController;
use App\Http\Controllers\Api\PaymentOptionController;
use App\Http\Controllers\Api\VaController;
use App\Http\Controllers\PosCategoryController;
use App\Http\Controllers\PosInventoryController;
use App\Http\Controllers\PosMainController;
use App\Http\Controllers\PosProfileController;
use App\Models\PosInventory;
use App\Models\PosInventoryLog;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/check/connection', function () {
    return response()->json([
        'message' => "Connected",
        "data" => now(),
    ], 200);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/check/phone', [AuthController::class, 'checkPhoneNumber']);
Route::post('/check/ktp', [AuthController::class, 'checkKtpNumber']);

Route::middleware('auth:sanctum')->group(function () {
    ///account Route
    Route::get('/user', [AuthController::class, 'getCurrentUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/gudang/user', [GudangController::class, 'getgudangByUser']);

    /* wishlist route */
    Route::prefix('wishlist')->group(function () {
        Route::get('/get/{id}', [WishlistController::class, 'getUserWishlist']);
        Route::post('/add', [WishlistController::class, 'addUserWishlist']);
        Route::post('/toggle', [WishlistController::class, 'toggleUserWishlist']);
        Route::delete('/remove/{id}', [WishlistController::class, 'removeUserWishlist']);
    });

    /* cart route */
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'getUserCart']);
        Route::post('/add', [CartController::class, 'createUserCart']);
        Route::get('/remove/{id}', [CartController::class, 'removeUserCart']);
        Route::post('/update/{id}/increase', [CartController::class, 'increaseUserCart']);
        Route::post('/update/{id}/decrease', [CartController::class, 'decrementUserCart']);
        Route::post('/clear', [CartController::class, 'clearCart']);
        Route::put('/update/item/{id}', [CartController::class, 'updateUserCart']);
        Route::delete('/remove-all', [CartController::class, 'removeAllUserCart']);
        Route::get('/get/quantity', [CartController::class, 'countCartQuantity']);
    });

    Route::prefix('daftar-alamat')->group(function () {
        Route::get('/', [DaftarAlamatController::class, 'getDaftarAlamatUser']);
        Route::get('/alamat/{id}', [DaftarAlamatController::class, 'getAlamatById']);
        Route::post('/add', [DaftarAlamatController::class, 'addAlamat']);
        Route::delete('/remove/{id}', [DaftarAlamatController::class, 'removeAlamat']);
        Route::get('/toggle/{id}', [DaftarAlamatController::class, 'toggleAlamat']);
        Route::put('/update/alamat/{id}', [DaftarAlamatController::class, 'updateAlamatById']);
    });

    Route::prefix('payment-option')->group(function () {
        Route::get('/{id}', [PaymentOptionController::class, 'getPaymentOptionById']);
        Route::get('/', [PaymentOptionController::class, 'getPaymentOptionByUser']);
    });

    ///Pesanan Routes
    Route::prefix('pesanan')->group(function () {
        Route::get('/user/{id}', [PesananController::class, 'getPesananUser']);
        Route::post('/create', [PesananController::class, 'createPesanan']);
        Route::post('/detail/create/{id}', [PesananController::class, 'createDetailPesanan']);
        Route::post('/{id}/transaksi', [TransaksiController::class, 'createTransaksi']);
        Route::put('/{id}/status/diterima', [PesananController::class, 'changeStatusDiterima']);
    });

    ///transaksi Routes
    Route::prefix('transaksi')->group(function () {
        Route::get('/{id}', [TransaksiController::class, 'getTransaksi']);
        Route::get('/user/{id}', [TransaksiController::class, 'getTransaksiListByUser']);
        Route::get('/detail/{id}', [TransaksiController::class, 'getDetailTransaksi']);
        Route::get('/pembayaran/{id}', [PaymentOptionController::class, 'getPaymentDetail']);
        Route::post('/bayar/{id}', [PaymentOptionController::class, 'changeStatusPembayaran']);
    });

    Route::prefix('user')->group(function () {
        Route::post('/change/branch', [BranchController::class, 'setBranchCompany']);
        Route::get('/get/branch', [BranchController::class, 'getBranchCompany']);
    });

    Route::prefix('kurir')->group(function () {
        Route::get('/all', [KurirController::class, 'getAllKurir']);
        Route::get('/user', [KurirController::class, 'getKurirByUser']);
        Route::get('/id/{id}', [KurirController::class, 'getKurirById']);
    });

    Route::get('token/stock/{id}', [StockController::class, 'getSingleStockWithUser']);
});

///Product Routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'getProducts']);
    Route::get('/{id}', [ProductController::class, 'getProduct']);
    Route::get('/category/{id}', [ProductController::class, 'getProductFromCategory']);
    Route::post('/search', [ProductController::class, 'searchProduct']);
    Route::post('/create', [ProductController::class, 'createProduct']);
});

///Category Routes
Route::prefix('categories')->group(function () {
    Route::get('/', [ProductController::class, 'getCategories']);
    Route::post('/search', [ProductController::class, 'searchCategory']);
    Route::post('/create', [ProductController::class, 'createCategory']);
});

///gudang Routes
Route::prefix('gudang')->group(function () {
    Route::get('/', [GudangController::class, 'getAllGudang']);
    Route::get('/{id}', [GudangController::class, 'getGudang']);
    Route::get('/{id}/kode', [GudangController::class, 'GetKodeCompanyByGudang']);
    Route::post('/search', [GudangController::class, 'searchGudang']);
});

///Stock Routes
Route::prefix('stock')->group(function () {
    Route::get('/', [StockController::class, 'getAllStocks']);
    Route::get('/gudang/{id}', [StockController::class, 'getStockFromGudang']);
    Route::get('/product/{id}', [StockController::class, 'getStockFromProduct']);
    Route::get('/category/{id}', [StockController::class, 'getStocksByCategory']);
    Route::get('/category/{cid}/gudang/{gid}', [StockController::class, 'getStocksByCategoryAndGudang']);
    Route::get('/unggulan', [StockController::class, 'getAllStockUnggulan']);
    Route::get('/unggulan/gudang/{id}', [StockController::class, 'getStockUnggulanFromGudangId']);
    Route::get('/{id}', [StockController::class, 'getSingleStock']);
    Route::post('/search/product', [StockController::class, 'searchStockByProductName']);
    Route::post('/search/category', [StockController::class, 'searchStockByCategoryName']);
    Route::post('/search/product/nologin', [StockController::class, 'searchStockByProductNameNoLogin']);
    Route::post('/search/category/nologin', [StockController::class, 'searchStockByCategoryNameNoLogin']);
});

///berita Routes
Route::prefix('berita')->group(function () {
    Route::get('/', [BeritaController::class, 'index']);
    Route::get('/{id}', [BeritaController::class, 'show']);
});

Route::prefix('banner')->group(function () {
    Route::get('/', [BannerController::class, 'index']);
    Route::get('/{id}', [BannerController::class, 'show']);
});

Route::prefix('virtual-account')->group(function () {
    Route::post('/get-token', [VaController::class, 'getToken']);
    Route::post('/generate-signature', [VaController::class, 'generateSignature']);
});

/* Daerah route */
Route::prefix('daerah')->group(function () {
    Route::get('/provinsi/all', [DaerahController::class, 'getAllProvinsi']);
    Route::get('/kota/all', [DaerahController::class, 'getAllKota']);
    Route::get('/kabupaten/all', [DaerahController::class, 'getAllKabupaten']);
    Route::get('/kecamatan/all', [DaerahController::class, 'getAllKecamatan']);
    Route::get('/kelurahan/all', [DaerahController::class, 'getAllKelurahan']);
    Route::get('/kota/id/{id}', [DaerahController::class, 'getKotaByProvinsiId']);
    Route::get('/kabupaten/id/{id}', [DaerahController::class, 'getKabupatenByProvinsiId']);
    Route::get('/kecamatan/id/{id}', [DaerahController::class, 'getKecamatanByKabupatenId']);
    Route::get('/kelurahan/id/{id}', [DaerahController::class, 'getKelurahanByKecamatanId']);
});

Route::prefix('debug')->group(function () {
    Route::get('/query/get', [DebugController::class, 'queryGet']);
});
