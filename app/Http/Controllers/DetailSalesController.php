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

    // Jika user pakai poin
    if ($request->check_poin) {
        $customer = customers::find($request->customer_id);
        $totalTransactions = saless::where('customer_id', $customer->id)->count();

        if ($totalTransactions == 1) {
            // Transaksi ke-2: gunakan poin awal 500
            $pointToUse = min(500, $sale->total_price);
        } else {
            // Transaksi selanjutnya: gunakan poin hasil dari transaksi sebelumnya
            $pointToUse = min($customer->point, $sale->total_price);
        }

        $sale->update([
            'total_point' => $pointToUse,
            'total_pay' => $sale->total_pay - $pointToUse,
            'total_return' => $sale->total_return + $pointToUse,
            'total_discount' => $sale->total_price - $pointToUse,
        ]);

        $customer->update([
            'name' => $request->name ?? $customer->name,
            'point' => ($totalTransactions == 1) ? $customer->point : 0 // Reset poin jika digunakan
        ]);
    }

    // Jika hanya update nama tanpa pakai poin
    if ($request->name && !$request->check_poin) {
        $customer = customers::find($request->customer_id);
        $customer->update(['name' => $request->name]);
    }

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
