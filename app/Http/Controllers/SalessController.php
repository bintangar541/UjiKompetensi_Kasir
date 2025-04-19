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
        $saless = saless::with('customer', 'user', 'detail_sales')->orderBy('id', 'desc')->get();
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

        session()->forget('shop');

        $selectedProducts = $request->shop;

        if (!is_array($selectedProducts)) {
            return back()->with('error', 'Format data tidak valid!');
        }

        $filteredProducts = collect($selectedProducts)
            ->mapWithKeys(function ($item) {
                $parts = explode(';', $item);
                if (count($parts) > 3) {
                    $id = $parts[0];
                    return [$id => $item];
                }
                return [];
            })
            ->values()
            ->toArray();

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
        $newreturn = $newPay - $newPrice;
    
        $detailSalesData = [];
    
        // Hitung point 1% dari total pembelanjaan
        $point = $request->member === 'Member' ? round($newPrice * 0.01, 2) : 0;
    
        if ($request->member === 'Member') {
            $existCustomer = customers::where('no_hp', $request->no_hp)->first();
    
            if ($existCustomer) {
                $existCustomer->update([
                    'point' => $existCustomer->point + $point,
                ]);
                $customer_id = $existCustomer->id;
            } else {
                $existCustomer = customers::create([
                    'name' => "",
                    'no_hp' => $request->no_hp,
                    'point' => $point,
                ]);
                $customer_id = $existCustomer->id;
            }
        } else {
            $customer_id = $request->customer_id;
        }
    
        $sales = saless::create([
            'sale_date' => Carbon::now()->format('Y-m-d'),
            'total_price' => $newPrice,
            'total_pay' => $newPay,
            'total_return' => $newreturn,
            'customer_id' => $customer_id,
            'user_id' => Auth::id(),
            'point' => $point,
            'total_point' => 0,
        ]);
    
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
    
        if ($request->member === 'Member') {
            return redirect()->route('sales.create.member', ['id' => $sales->id])
                ->with('message', 'Silahkan daftar sebagai member');
        } else {
            return redirect()->route('sales.print.show', ['id' => $sales->id])
                ->with('message', 'Silahkan Print');
        }
    }




    /**
     * Display the specified resource.
     */
    public function createmember(Request $request, $id)
    {
        $sale = saless::with('detail_sales.product', 'customer')->findOrFail($id);

        $notFirst = saless::where('customer_id', $sale->customer->id)->where('id', '<>', $sale->id)->exists();

        $previousSales = saless::where('customer_id', $sale->customer->id)
            ->where('id', '<', $sale->id)
            ->get();

        $previousPoints = $previousSales->sum('point');

        $usePoints = $request->has('check_poin') && $request->input('check_poin') === 'Ya';

        if ($usePoints) {
            $availablePoints = $previousPoints;
            $pointsToUse = (int) $request->input('use_point');
            $sale->customer->update([
                'point' => $sale->customer->point - $pointsToUse
            ]);
            $sale->update([
                'point_used' => $pointsToUse
            ]);
            $sale->update([
                'total_price' => $sale->total_price - $pointsToUse
            ]);
        }
        return view('sales.view_member', compact('sale', 'notFirst', 'previousPoints'));
    }




    public function member(Request $request) {}



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
