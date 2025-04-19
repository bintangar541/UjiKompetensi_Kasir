<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Ambil data user tanpa 'created_at'
        return User::select( 'name', 'email', 'role')->get();
    }

    public function headings(): array
    {
        // Menghilangkan 'Created At' dari heading
        return [
            'Nama',
            'Email',
            'Role'
        ];
    }
}
