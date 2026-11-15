<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // 'role' & 'is_active' SENGAJA tidak mass-assignable
    // → mencegah privilege escalation lewat mass assignment.
    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed', // bcrypt otomatis (config/hashing.php)
            'is_active'         => 'boolean',
            'last_login_at'     => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }
}