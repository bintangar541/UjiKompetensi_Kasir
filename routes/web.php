<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\SalessController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('main');
});

Route::get('/product', [ProductsController::class, 'index'])->name('product.index');

Route::get('/sales', [SalessController::class, 'index'])->name('sales.index');

Route::get('/sales', [SalessController::class, 'index'])->name('sales.index');

