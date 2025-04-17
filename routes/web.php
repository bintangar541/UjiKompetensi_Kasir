<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SalessController;
use App\Http\Controllers\DetailSalesController;

// Halaman utama


Route::get('/dashboard', function () {
    return view('dashboard.index');
})->middleware('auth');


Route::get('/login', [UserController::class, 'login'])->name('login');
Route::post('/login', [UserController::class, 'loginPost'])->name('login.post');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');
/**
 * ROUTE UNTUK PRODUK
 */
Route::prefix('product')->name('product.')->group(function () {
    Route::get('/', [ProductsController::class, 'index'])->name('index');
    Route::get('/create', [ProductsController::class, 'create'])->name('create');
    Route::post('/store', [ProductsController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [ProductsController::class, 'edit'])->name('edit');
    Route::put('/edit/{id}', [ProductsController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProductsController::class, 'destroy'])->name('delete');
    Route::put('/edit-stock/{id}', [ProductsController::class, 'updateStock'])->name('stock');
});

/**
 * ROUTE UNTUK USER
 */
Route::prefix('user')->name('user.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/store', [UserController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [UserController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('delete');
});

/**
 * ROUTE UNTUK SALES
 */

 Route::prefix('/sales')->name('sales.')->group(function () {
    Route::get('/', [SalessController::class, 'index'])->name('index');
    Route::get('/create',[SalessController::class, 'create'])->name('create');
    Route::post('/create/post',[SalessController::class, 'store'])->name('store');
    Route::post('/create/post/createsales',[SalessController::class, 'createsales'])->name('createsales');
    Route::get('/create/post',[SalessController::class, 'post'])->name('post');
    Route::get('/print/{id}',[DetailSalesController::class, 'show'])->name('print.show');
    Route::get('/create/member/{id}', [SalessController::class, 'createmember'])->name('create.member');
    Route::get('/exportexcel', [DetailSalesController::class, 'exportexcel'])->name('exportexcel');
});
