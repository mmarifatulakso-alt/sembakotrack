@extends('layouts.app')
@section('title', 'Ubah Target Harga')

@section('content')
<div class="card narrow">
    <h1>Ubah Target: {{ $watchlist->komoditas->nama }}</h1>
    <form method="POST" action="{{ route('watchlist.update', $watchlist->id) }}">
        @csrf
        @method('PUT')

        <label for="target_harga">Target harga (Rp)</label>
        <input id="target_harga" type="number" name="target_harga" min="100" max="100000000" step="1" required
               value="{{ old('target_harga', (int) $watchlist->target_harga) }}">
        @error('target_harga')<div class="err">{{ $message }}</div>@enderror

        <label for="catatan">Catatan (opsional)</label>
        <input id="catatan" type="text" name="catatan" maxlength="255" value="{{ old('catatan', $watchlist->catatan) }}">
        @error('catatan')<div class="err">{{ $message }}</div>@enderror

        <p>
            <button class="btn" type="submit">Simpan</button>
            <a class="btn btn-sec" href="{{ route('watchlist.index') }}">Batal</a>
        </p>
    </form>
</div>
@endsection