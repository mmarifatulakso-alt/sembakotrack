<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Watchlist extends Model
{
    // user_id TIDAK fillable: selalu diisi lewat relasi $user->watchlists()->create()
    protected $fillable = ['komoditas_id', 'target_harga', 'catatan'];

    protected function casts(): array
    {
        return ['target_harga' => 'float'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function komoditas(): BelongsTo
    {
        return $this->belongsTo(Komoditas::class, 'komoditas_id');
    }
}