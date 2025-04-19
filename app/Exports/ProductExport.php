<?php

namespace App\Exports;

use App\Models\products;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return products::all();
    }

    public function headings(): array
    {
        return ['Nama Produk', 'Harga', 'Stok'];
    }

    public function map($product): array
    {
        return [
            $product->name,
            'Rp ' . number_format($product->price, 0, ',', '.'),
            $product->stock,
        ];
    }
}
