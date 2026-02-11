<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/seller/login', [AuthController::class, 'sellerLogin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/sellers', [AdminController::class, 'createSeller']);
    Route::get('/admin/sellers', [AdminController::class, 'listSellers']);

    Route::post('/seller/products', [ProductController::class, 'addProduct']);
    Route::get('/seller/products', [ProductController::class, 'listProducts']);
    Route::get('/seller/products/{productId}/pdf', [ProductController::class, 'viewPdf']);
    Route::delete('/seller/products/{productId}', [ProductController::class, 'deleteProduct']);
});
