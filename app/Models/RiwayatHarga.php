<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatHarga extends Model
{
    protected $table = 'riwayat_harga';

    protected $fillable = ['komoditas_id', 'tanggal', 'harga', 'sumber'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'harga' => 'float'];
    }

    public function komoditas(): BelongsTo
    {
        return $this->belongsTo(Komoditas::class, 'komoditas_id');
    }
}