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

    $customer_id = null;
    $earnedPoint = 0;
    $usedPoint = 0;

    if ($request->member === 'Member') {
        $customer = customers::where('no_hp', $request->no_hp)->first();

        if (!$customer) {
            // Transaksi pertama (belum pernah beli)
            $customer = customers::create([
                'name' => "",
                'no_hp' => $request->no_hp,
                'point' => 0,
            ]);
        } else {
            // Ada transaksi sebelumnya?
            $lastSale = saless::where('customer_id', $customer->id)->latest()->first();

            if ($lastSale) {
                if ($request->has('check_poin')) {
                    // Gunakan poin sebelumnya
                    $usedPoint = $customer->point;
                    $newPrice -= $usedPoint;
                    $customer->update(['point' => 0]);
                }
            }

            // Dapatkan poin dari transaksi sekarang (1% dari total belanja SETELAH dikurangi poin)
            $earnedPoint = floor($newPrice * 0.01);
        }

        $customer_id = $customer->id;
    } else {
        // Untuk pelanggan non-member (bisa diabaikan atau sesuaikan logika)
        $customer_id = $request->customer_id;
        $customer = customers::find($customer_id);
        $earnedPoint = floor($newPrice * 0.01);
        if ($customer) {
            $customer->update(['point' => 0]);
        }
    }

    // Simpan transaksi
    $sales = saless::create([
        'sale_date' => now()->format('Y-m-d'),
        'total_price' => $newPrice,
        'total_pay' => $newPay,
        'total_return' => $newReturn,
        'customer_id' => $customer_id,
        'user_id' => Auth::id(),
        'point' => $earnedPoint,     // Poin yang didapat, untuk keperluan internal (tidak ditampilkan)
        'total_point' => $usedPoint, // Poin yang digunakan
    ]);

    // Simpan detail pembelian
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

    // Simpan poin yang baru didapat untuk digunakan di transaksi selanjutnya
    if ($customer) {
        $customer->update([
            'point' => $customer->point + $earnedPoint
        ]);
    }

    if ($request->member === 'Member') {
        return redirect()->route('sales.create.member', ['id' => $sales->id])
            ->with('point_used', $usedPoint)
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
