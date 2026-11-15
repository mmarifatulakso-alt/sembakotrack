<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Watchlist;

class WatchlistPolicy
{
    /** Hanya PEMILIK yang boleh mengubah/menghapus. Admin pun tidak. */
    public function manage(User $user, Watchlist $watchlist): bool
    {
        return $user->id === $watchlist->user_id;
    }
}