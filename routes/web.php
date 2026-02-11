<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UiController;

Route::get('/', [UiController::class, 'home'])->name('home');

Route::get('/admin/login', [UiController::class, 'adminLoginForm'])->name('admin.login.form');
Route::post('/admin/login', [UiController::class, 'adminLogin'])->name('admin.login');
Route::post('/admin/logout', [UiController::class, 'adminLogout'])->name('admin.logout');

Route::get('/seller/login', [UiController::class, 'sellerLoginForm'])->name('seller.login.form');
Route::post('/seller/login', [UiController::class, 'sellerLogin'])->name('seller.login');
Route::post('/seller/logout', [UiController::class, 'sellerLogout'])->name('seller.logout');

Route::get('/admin/sellers', [UiController::class, 'adminSellers'])->name('admin.sellers');
Route::post('/admin/sellers', [UiController::class, 'createSeller'])->name('admin.sellers.create');

Route::get('/seller/products', [UiController::class, 'sellerProducts'])->name('seller.products');
Route::post('/seller/products', [UiController::class, 'createProduct'])->name('seller.products.create');
Route::delete('/seller/products/{productId}', [UiController::class, 'deleteProduct'])
    ->name('seller.products.delete');
Route::get('/seller/products/{productId}/pdf', [UiController::class, 'downloadProductPdf'])
    ->name('seller.products.pdf');
