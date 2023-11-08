<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;

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

Route::post('/register',[AuthController::class, 'register']);
Route::post('/login',[AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    ///account Route
    Route::post('/user', [AuthController::class, 'getCurrentUser']);
    Route::post('/logout', [AuthController::class, 'logout']);

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
});
