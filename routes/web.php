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

    $memberCount = 0;
    $nonMemberCount = 0;

    if ($user->role === 'employee') {
        $todaySalesCount = DB::table('saless')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $memberCount = DB::table('customers')->count();

        $nonMemberCount = DB::table('saless')
            ->whereNull('customer_id')
            ->count();
    }

    if ($user->role === 'admin') {
        // LINE CHART - Penjualan per Hari di Bulan Ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now();

        $allDates = [];
        $currentDate = $startOfMonth->copy();
        while ($currentDate <= $endOfMonth) {
            $allDates[$currentDate->format('Y-m-d')] = 0;
            $currentDate->addDay();
        }

        $sales = DB::table('saless')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        foreach ($sales as $sale) {
            $allDates[$sale->date] = $sale->total;
        }

        foreach ($allDates as $date => $count) {
            $labels[] = Carbon::parse($date)->format('d M');
            $salesData[] = $count;
        }

        // PIE CHART - Penjualan per Produk (dibulatkan)
        $productSales = DB::table('detail_sales')
            ->join('products', 'detail_sales.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('count(detail_sales.id) as total'))
            ->groupBy('products.name')
            ->orderByDesc('total')
            ->get();

        $totalProductSales = $productSales->sum('total');

        $labelspieChart = [];
        $salesDatapieChart = [];

        foreach ($productSales as $item) {
            $labelspieChart[] = $item->name;
            $salesDatapieChart[] = $totalProductSales > 0 
                ? round(($item->total / $totalProductSales) * 100)
                : 0;
        }
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
    Route::get('/products/export', [ProductsController::class, 'export'])->name('export');

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
    Route::get('/export', [UserController::class, 'export'])->name('export');
});


/**
 * ROUTE UNTUK SALES
 */
// Route::prefix('/sales')->name('sales.')->group(function () {


    Route::prefix('/sales')->name('sales.')->group(function () {
        // HANYA ADMIN atau EMPLOYEE yang boleh mengakses INDEX
        Route::get('/', [SalessController::class, 'index'])
            ->middleware('role:admin,employee') // ⬅️ Ini untuk admin dan employee
            ->name('index');
        
        // HANYA EMPLOYEE yang boleh akses route CREATE dan sejenisnya
        Route::middleware(['role:employee'])->group(function () {
            Route::get('/create', [SalessController::class, 'create'])->name('create');
            Route::post('/create/post', [SalessController::class, 'store'])->name('store');
            Route::post('/create/post/createsales', [SalessController::class, 'createsales'])->name('createsales');
            Route::get('/create/post', [SalessController::class, 'post'])->name('post');
            Route::get('/create/member/{id}', [SalessController::class, 'createmember'])->name('create.member');
        });
        
        // Semua login user bisa akses
        Route::get('/print/{id}', [DetailSalesController::class, 'show'])->name('print.show');
        Route::get('/exportexcel', [DetailSalesController::class, 'exportexcel'])->name('exportexcel');
        Route::get('/sales/download/{id}', [DetailSalesController::class, 'downloadPDF'])->name('downloadpdf');


        Route::get('/sales/export', [DetailSalesController::class, 'export'])->name('export');
    });
    
    

