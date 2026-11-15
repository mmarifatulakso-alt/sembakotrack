<?php

namespace App\Http\Controllers;

use App\Http\Requests\WatchlistRequest;
use App\Models\Komoditas;
use App\Models\Watchlist;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function index(Request $request): View
    {
        // READ: hanya milik user yang login (query di-scope lewat relasi)
        $items = $request->user()->watchlists()
            ->with('komoditas.latestRiwayat')
            ->latest()
            ->get();

        // Komoditas dari database real yang belum dipantau user
        $tersedia = Komoditas::whereNotIn('id', $items->pluck('komoditas_id'))
            ->orderBy('nama')
            ->get();

        return view('watchlist.index', compact('items', 'tersedia'));
    }

    public function store(WatchlistRequest $request): RedirectResponse
    {
        // user_id diisi otomatis dari relasi, BUKAN dari input form
        $w = $request->user()->watchlists()->create($request->validated());

        $this->audit->log('watchlist.create', 'Menambah pantauan', ['watchlist_id' => $w->id]);

        return redirect()->route('watchlist.index')->with('status', 'Komoditas ditambahkan ke pantauan.');
    }

    public function edit(Request $request, int $watchlist): View
    {
        $w = $this->ownedOrFail($request, $watchlist);

        return view('watchlist.edit', ['watchlist' => $w->load('komoditas')]);
    }

    public function update(WatchlistRequest $request, int $watchlist): RedirectResponse
    {
        $w = $this->ownedOrFail($request, $watchlist);
        $w->update($request->validated()); // hanya target_harga & catatan

        $this->audit->log('watchlist.update', 'Mengubah target harga', ['watchlist_id' => $w->id]);

        return redirect()->route('watchlist.index')->with('status', 'Target harga diperbarui.');
    }

    public function destroy(Request $request, int $watchlist): RedirectResponse
    {
        $w = $this->ownedOrFail($request, $watchlist);
        $w->delete();

        $this->audit->log('watchlist.delete', 'Menghapus pantauan', ['watchlist_id' => $watchlist]);

        return redirect()->route('watchlist.index')->with('status', 'Pantauan dihapus.');
    }

    /**
     * ANTI-IDOR: setiap akses ke record dicek kepemilikannya lewat Policy.
     * Jika ID milik user lain → dicatat ke audit log & dibalas 404
     * (bukan 403, supaya penyerang tidak bisa menebak ID yang valid).
     */
    private function ownedOrFail(Request $request, int $id): Watchlist
    {
        $w = Watchlist::find($id);
        abort_if(! $w, 404);

        if (Gate::denies('manage', $w)) {
            $this->audit->log('idor.blocked', 'Percobaan akses watchlist milik user lain', [
                'watchlist_id' => $id,
                'method'       => $request->method(),
            ]);
            abort(404);
        }

        return $w;
    }
}