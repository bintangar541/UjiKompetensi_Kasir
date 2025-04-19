<?php

namespace App\Http\Controllers;

use App\Exports\salesimport;
use App\Models\customers;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;
use App\Models\saless;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
// use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

class DetailSalesController extends Controller
{
    // Tampilkan detail transaksi untuk keperluan cetak
    public function show(Request $request, $id)
{
    $sale = saless::with(['detail_sales.product', 'customer'])->findOrFail($id);
    $customer = $sale->customer;

    // Hitung jumlah transaksi sebelum ini
    $totalTransactions = saless::where('customer_id', $customer->id)
        ->where('id', '<', $sale->id)
        ->count();

    // Ambil poin dari transaksi sebelumnya
    if ($totalTransactions >= 1) {
        $customer->usable_point += $customer->pending_point;
        $customer->pending_point = 0; // reset pending karena sudah dipindah ke usable
    }

    $usedPoint = 0;
    if ($request->check_poin) {
        $usedPoint = min($customer->usable_point, $sale->total_price);

        // Update transaksi
        $sale->update([
            'total_point'    => $usedPoint,
            'total_pay'      => $sale->total_pay - $usedPoint,
            'total_return'   => $sale->total_return + $usedPoint,
            'total_discount' => $sale->total_price - $usedPoint,
        ]);

        // Kurangi poin yang digunakan
        $customer->usable_point -= $usedPoint;
    }

    // Update nama jika dikirim
    if ($request->name && !$request->check_poin) {
        $customer->update(['name' => $request->name]);
    }

    // Simpan poin baru dari transaksi ini (1%)
    $newPoints = round($sale->total_price * 0.01);
    $customer->pending_point += $newPoints;

    $customer->save();

    return view('sales.print_sale', compact('sale'));
}





    // Unduh PDF transaksi
    public function downloadPDF($id)
    {
        try {
            $sale = saless::with(['detail_sales.product', 'customer'])->findOrFail($id);

            $pdf = Pdf::loadView('sales.download', ['sale' => $sale]);


            Log::info('PDF berhasil diunduh untuk transaksi dengan ID ' . $id);
            return $pdf->download('Surat_receipt.pdf');
            
        } catch (\Exception $e) {
            Log::error('Gagal mengunduh PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh PDF');
        }
    }

    public function export()
    {
        return Excel::download(new SalesExport, 'sales.xlsx');
    }

}
