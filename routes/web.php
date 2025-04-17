<?php

use App\Models\saless;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SalessController;
use App\Http\Controllers\DetailSalesController;

// Halaman utama


Route::get('/dashboard', function () {
    $user = Auth::user();

    $todaySalesCount = null;
    $labels = [];
    $salesData = [];
    $labelspieChart = [];
    $salesDatapieChart = [];

    if ($user->role === 'employee') {
        $todaySalesCount = DB::table('saless')
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Hitung jumlah member terdaftar
        $memberCount = DB::table('customers')->count();

        // Hitung jumlah non-member (jumlah penjualan tanpa customer_id)
        $nonMemberCount = DB::table('saless')
    ->whereNull('customer_id')
    ->count();


        // Atau kalau no_hp tidak disimpan, hitung berdasarkan transaksi
        $nonMemberCount = DB::table('saless')
            ->whereNull('customer_id')
            ->count();
    }



    if ($user->role === 'admin') {
        // LINE CHART - Penjualan per Hari di Bulan Ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now();

        // Ambil semua tanggal dalam bulan ini
        $allDates = [];
        $currentDate = $startOfMonth->copy();
        while ($currentDate <= $endOfMonth) {
            $allDates[$currentDate->format('Y-m-d')] = 0;
            $currentDate->addDay();
        }

        // Ambil data penjualan per hari
        $sales = DB::table('saless')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        foreach ($sales as $sale) {
            $allDates[$sale->date] = $sale->total;
        }

        // Buat labels dan data untuk line chart
        $labels = [];
        $salesData = [];
        foreach ($allDates as $date => $count) {
            $labels[] = Carbon::parse($date)->format('d M');
            $salesData[] = $count;
        }

        // PIE CHART - Penjualan per Produk
        $productSales = DB::table('detail_sales')
        ->join('products', 'detail_sales.product_id', '=', 'products.id')
        ->select('products.name', DB::raw('count(detail_sales.id) as total'))
        ->groupBy('products.name')
        ->orderByDesc('total')
        ->get();

    $labelspieChart = $productSales->pluck('name')->toArray();
    $salesDatapieChart = $productSales->pluck('total')->toArray();
    }

    return view('dashboard.index', compact(
        'todaySalesCount',
        'labels',
        'salesData',
        'labelspieChart',
        'salesDatapieChart',
        'memberCount',
        'nonMemberCount'
    ));


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

 Route::prefix('user')->name('user.')->middleware('role:admin')->group(function () {
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
// Route::prefix('/sales')->name('sales.')->group(function () {


    Route::prefix('/sales')->name('sales.')->group(function () {
    Route::get('/', [SalessController::class, 'index'])->name('index');
    Route::get('/create',[SalessController::class, 'create'])->name('create');
    Route::post('/create/post',[SalessController::class, 'store'])->name('store');
    Route::post('/create/post/createsales',[SalessController::class, 'createsales'])->name('createsales');
    Route::get('/create/post',[SalessController::class, 'post'])->name('post');
    Route::get('/print/{id}',[DetailSalesController::class, 'show'])->name('print.show');
    Route::get('/create/member/{id}', [SalessController::class, 'createmember'])->name('create.member');
    Route::get('/exportexcel', [DetailSalesController::class, 'exportexcel'])->name('exportexcel');
    Route::get('/download/{id}', [DetailSalesController::class, 'downloadPDF'])->name('download');
    Route::get('/sales/export', [DetailSalesController::class, 'export'])->name('export');

});

