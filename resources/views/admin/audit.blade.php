@extends('layouts.app')
@section('title', 'Audit Log')

@section('content')
<h1>Audit Log</h1>

<div class="card">
    <form method="GET" action="{{ route('admin.audit') }}" class="toolbar">
        <select name="event" aria-label="Filter event">
            <option value="">Semua event</option>
            @foreach($events as $e)
                <option value="{{ $e }}" @selected(request('event') === $e)>{{ $e }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm" type="submit">Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Waktu (WIB)</th>
                <th>Event</th>
                <th>User</th>
                <th>IP</th>
                <th>Keterangan</th>
                <th>User-Agent</th>
            </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->event }}</td>
                <td>{{ $log->user?->email ?? '-' }}</td>
                <td>{{ $log->ip_address ?? '-' }}</td>
                <td>{{ $log->description }}</td>
                <td class="muted">{{ \Illuminate\Support\Str::limit($log->user_agent, 40) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">Tidak ada log.</td></tr>
        @endforelse
        </tbody>
    </table>

    @if ($logs->hasPages())
        <div class="toolbar" style="margin-top: 1rem; justify-content: space-between; align-items: center; display: flex;">
            <span class="muted">
                Menampilkan {{ $logs->firstItem() }} – {{ $logs->lastItem() }} dari {{ $logs->total() }} log
            </span>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                @if ($logs->onFirstPage())
                    <span class="btn btn-sm" style="opacity: 0.5; pointer-events: none;">&laquo; Sebelumnya</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="btn btn-sm">&laquo; Sebelumnya</a>
                @endif

                <span class="muted" style="padding: 0 0.5rem;">
                    Hal {{ $logs->currentPage() }} / {{ $logs->lastPage() }}
                </span>

                @if ($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="btn btn-sm">Berikutnya &raquo;</a>
                @else
                    <span class="btn btn-sm" style="opacity: 0.5; pointer-events: none;">Berikutnya &raquo;</span>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection