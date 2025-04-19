<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class customers extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'no_hp',
        'point',           // (boleh dipakai untuk histori poin total jika ingin)
        'usable_point',    // poin yang bisa dipakai saat ini
        'pending_point',   // poin yang baru didapat, baru bisa dipakai di transaksi berikutnya
    ];

    public function saless()
    {
        return $this->hasMany(saless::class);
    }
}
