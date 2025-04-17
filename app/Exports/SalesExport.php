<?php

namespace App\Exports;

use App\Models\saless;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return saless::with(['customer', 'user', 'detail_sales.product'])->get()->map(function ($sale) {
            $productNames = $sale->detail_sales->map(function ($detail) {
                return $detail->product->name ?? 'Produk tidak ditemukan';
            })->implode(', '); // Gabungkan nama produk jadi 1 string

            return [
                'Nama Pelanggan'    => $sale->customer->name ?? 'NON-MEMBER',
                'Tanggal Penjualan' => $sale->sale_date,
                'Total Harga'       => $sale->total_price,
                'Nama Produk'       => $productNames,
                'Dibuat Oleh'       => optional($sale->user)->name ?? 'Pegawai Tidak Ada',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Pelanggan',
            'Tanggal Penjualan',
            'Total Harga',
            'Nama Produk',
            'Dibuat Oleh',
        ];
    }
}
