<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Komoditas extends Model
{
    protected $table = 'komoditas';

    protected $fillable = ['kode', 'nama', 'satuan', 'kategori'];

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatHarga::class, 'komoditas_id');
    }

    public function latestRiwayat(): HasOne
    {
        return $this->hasOne(RiwayatHarga::class, 'komoditas_id')->latestOfMany('tanggal');
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class, 'komoditas_id');
    }
}