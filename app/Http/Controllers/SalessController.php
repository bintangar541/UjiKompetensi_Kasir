<?php

namespace App\Http\Controllers;

use App\Models\customers;
use App\Models\detail_sales;
use App\Models\products;
use App\Models\saless;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $saless = saless::with('customer', 'user', 'detail_sales')->orderBy('id','desc')->get();
        return view('sales.index', compact('saless'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = products::all();
        return view('sales.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->has('shop')) {
            return back()->with('error', 'Pilih produk terlebih dahulu!');
        }

        // Hapus data sebelumnya agar tidak terjadi duplikasi
        session()->forget('shop');

        $selectedProducts = $request->shop;

        // Pastikan data dikirim dalam bentuk array
        if (!is_array($selectedProducts)) {
            return back()->with('error', 'Format data tidak valid!');
        }

        // Simpan hanya produk yang memiliki jumlah lebih dari 0, hapus duplikasi
        $filteredProducts = collect($selectedProducts)
            ->mapWithKeys(function ($item) {
                $parts = explode(';', $item);
                if (count($parts) > 3) {
                    $id = $parts[0];
                    return [$id => $item]; // Pastikan hanya 1 produk per ID
                }
                return [];
            })
            ->values()
            ->toArray();

        // Simpan ke sesi
        session(['shop' => $filteredProducts]);

        return redirect()->route('sales.post');
    }


    public function post()
    {
        $shop = session('shop', []);
        return view('sales.detail', compact('shop'));
    }

    public function createsales(Request $request)
{
    $request->validate([
        'total_pay' => 'required',
    ], [
        'total_pay.required' => 'Berapa jumlah uang yang dibayarkan?',
    ]);

    $newPrice = (int) preg_replace('/\D/', '', $request->total_price);
    $newPay = (int) preg_replace('/\D/', '', $request->total_pay);
    $newReturn = $newPay - $newPrice;

    // Default customer_id = null
    $customer_id = null;
    $earnedPoint = 0;
    $usedPoint = $request->has('check_poin') ? 1 : 0;

    if ($request->member === 'Member') {
        // Cek apakah customer sudah ada
        $customer = customers::where('no_hp', $request->no_hp)->first();

        if (!$customer) {
            // Customer baru (pembelian pertama)
            $customer = customers::create([
                'name' => "",
                'no_hp' => $request->no_hp,
                'point' => 500, // Bonus awal
            ]);
            $earnedPoint = 0; // Transaksi pertama tidak dapat 1%
        } else {
            // Transaksi ke-2 atau selanjutnya
            $earnedPoint = floor($newPrice * 0.01);
            $customer->update([
                'point' => $customer->point + $earnedPoint,
            ]);
        }

        $customer_id = $customer->id;
    } else {
        $customer_id = $request->customer_id;
        $customer = customers::find($customer_id);
        $earnedPoint = floor($newPrice * 0.01);
        if ($customer) {
            $customer->update([
                'point' => $customer->point + $earnedPoint,
            ]);
        }
    }

    // Simpan data penjualan
    $sales = saless::create([
        'sale_date' => now()->format('Y-m-d'),
        'total_price' => $newPrice,
        'total_pay' => $newPay,
        'total_return' => $newReturn,
        'customer_id' => $customer_id,
        'user_id' => Auth::id(),
        'point' => $earnedPoint,
        'total_point' => 0,
    ]);

    $detailSalesData = [];

    foreach ($request->shop as $shopItem) {
        $item = explode(';', $shopItem);
        $productId = (int) $item[0];
        $amount = (int) $item[3];
        $subtotal = (int) $item[4];

        $detailSalesData[] = [
            'sale_id' => $sales->id,
            'product_id' => $productId,
            'amount' => $amount,
            'subtotal' => $subtotal,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Kurangi stok
        $product = products::find($productId);
        if ($product) {
            $newStock = $product->stock - $amount;
            if ($newStock < 0) {
                return redirect()->back()->withErrors(['error' => 'Stok tidak mencukupi untuk produk ' . $product->name]);
            }
            $product->update(['stock' => $newStock]);
        }
    }

    detail_sales::insert($detailSalesData);

    // Redirect
    if ($request->member === 'Member') {
        return redirect()->route('sales.create.member', ['id' => $sales->id])
            ->with('message', 'Silahkan daftar sebagai member');
    } else {
        return redirect()->route('sales.print.show', ['id' => $sales->id])
            ->with('message', 'Silahkan cetak struk');
    }



    }


    /**
     * Display the specified resource.
     */
    public function createmember($id)
    {
        $sale = saless::with('detail_sales.product')->findOrFail($id);
        // Menentukan apakah customer sudah pernah melakukan pembelian sebelumnya
        $notFirst = saless::where('customer_id', $sale->customer->id)->count() != 1 ? true : false;
        return view('sales.view_member', compact('sale','notFirst'));
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(saless $saless)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, saless $saless)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(saless $saless)
    {
        //
    }
}
