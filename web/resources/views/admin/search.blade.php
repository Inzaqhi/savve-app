@extends('layouts.admin')
@section('title', 'Hasil Pencarian')

@section('content')

<div class="anim-fade-up delay-1 mb-6">
    <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #1a3a6b">Pencarian</p>
    <h1 class="text-xl lg:text-2xl font-black text-gray-900">
        Hasil untuk "{{ $query }}"
    </h1>
    <p class="text-gray-400 text-sm mt-1">
        {{ $transaksis->count() + $events->count() + $kasirs->count() }} hasil ditemukan
    </p>
</div>

{{-- Search Bar Besar --}}
<div class="anim-fade-up delay-2 bg-white rounded-2xl border border-gray-100 p-4 mb-6"
    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
    <form method="GET" action="{{ route('admin.search') }}">
        <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
            <input type="text" name="q" value="{{ $query }}" autofocus
                class="w-full rounded-xl pl-11 pr-4 py-3.5 text-sm transition"
                style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #374151"
                placeholder="Cari transaksi, penitip, event, kasir..."
                onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
        </div>
    </form>
</div>

@if(strlen($query) < 2)
<div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 p-12 text-center"
    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
    <div class="text-4xl mb-3">🔍</div>
    <p class="font-semibold text-gray-400">Ketik minimal 2 karakter untuk mencari.</p>
</div>
@else

{{-- Hasil Transaksi --}}
@if($transaksis->count() > 0)
<div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 overflow-hidden mb-4"
    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
        <span class="text-base">📋</span>
        <p class="font-black text-gray-800">Transaksi</p>
        <span class="ml-auto text-xs font-bold px-2 py-1 rounded-full"
            style="background: #eff6ff; color: #1d4ed8">{{ $transaksis->count() }} hasil</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" style="min-width: 500px">
            <thead>
                <tr style="background: #f8faff; border-bottom: 1px solid #e2e8f0">
                    <th class="px-5 py-3 text-left whitespace-nowrap" style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">No. Transaksi</th>
                    <th class="px-5 py-3 text-left whitespace-nowrap" style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">Penitip</th>
                    <th class="px-5 py-3 text-left whitespace-nowrap" style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">Event</th>
                    <th class="px-5 py-3 text-left whitespace-nowrap" style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">Status</th>
                    <th class="px-5 py-3 text-right whitespace-nowrap" style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">Total</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaksis as $t)
                <tr class="table-row" style="border-top: 1px solid #f1f5f9">
                    <td class="px-5 py-3 whitespace-nowrap">
                        <a href="{{ route('admin.transaksis.show', $t) }}"
                            class="font-bold hover:underline font-mono"
                            style="color: #1a3a6b; font-size: 12px">
                            {{ $t->nomor_transaksi }}
                        </a>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-white flex-shrink-0"
                                style="background: linear-gradient(135deg, #0f2044, #4a9eff); font-size: 10px">
                                {{ strtoupper(substr($t->nama_penitip, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700 text-sm">{{ $t->nama_penitip }}</p>
                                <p class="text-gray-400" style="font-size: 11px">{{ $t->no_whatsapp }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs whitespace-nowrap">
                        {{ Str::limit($t->event->nama_event, 20) }}
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <span class="px-3 py-1 rounded-full text-xs font-bold"
                            style="background: {{ $t->status === 'dititip' ? '#eff6ff' : '#f0fdf4' }};
                                   color: {{ $t->status === 'dititip' ? '#1d4ed8' : '#15803d' }}">
                            {{ $t->status === 'dititip' ? 'DITITIPKAN' : 'DIAMBIL' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-bold whitespace-nowrap" style="color: #0f2044">
                        Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.transaksis.show', $t) }}"
                            class="text-gray-300 hover:text-indigo-500 text-lg">⋯</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Hasil Event --}}
@if($events->count() > 0)
<div class="anim-fade-up delay-4 bg-white rounded-2xl border border-gray-100 overflow-hidden mb-4"
    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
        <span class="text-base">📅</span>
        <p class="font-black text-gray-800">Event</p>
        <span class="ml-auto text-xs font-bold px-2 py-1 rounded-full"
            style="background: #eff6ff; color: #1d4ed8">{{ $events->count() }} hasil</span>
    </div>
    <div class="divide-y divide-gray-50">
        @foreach($events as $event)
        <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
            <div>
                <p class="font-bold text-gray-800">{{ $event->nama_event }}</p>
                <p class="text-xs text-gray-400">
                    {{ $event->tanggal_mulai->format('d M Y') }} — {{ $event->tanggal_selesai->format('d M Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full text-xs font-bold"
                    style="background: {{ $event->status === 'aktif' ? '#f0fdf4' : '#f8faff' }};
                           color: {{ $event->status === 'aktif' ? '#15803d' : '#94a3b8' }}">
                    {{ $event->status === 'aktif' ? '● Aktif' : '● Selesai' }}
                </span>
                <a href="{{ route('admin.events.edit', $event) }}"
                    class="text-xs font-bold hover:underline" style="color: #1a3a6b">Edit →</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Hasil Kasir --}}
@if($kasirs->count() > 0)
<div class="anim-fade-up delay-5 bg-white rounded-2xl border border-gray-100 overflow-hidden mb-4"
    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
        <span class="text-base">👤</span>
        <p class="font-black text-gray-800">Kasir</p>
        <span class="ml-auto text-xs font-bold px-2 py-1 rounded-full"
            style="background: #eff6ff; color: #1d4ed8">{{ $kasirs->count() }} hasil</span>
    </div>
    <div class="divide-y divide-gray-50">
        @foreach($kasirs as $kasir)
        <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-sm text-white flex-shrink-0"
                    style="background: linear-gradient(135deg, #0f2044, #4a9eff)">
                    {{ strtoupper(substr($kasir->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-gray-800">{{ $kasir->name }}</p>
                    <p class="text-xs text-gray-400">{{ $kasir->email }}</p>
                </div>
            </div>
            <a href="{{ route('admin.users.edit', $kasir) }}"
                class="text-xs font-bold hover:underline" style="color: #1a3a6b">Edit →</a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Tidak ada hasil --}}
@if($transaksis->count() === 0 && $events->count() === 0 && $kasirs->count() === 0)
<div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 p-12 text-center"
    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
    <div class="text-4xl mb-3">😕</div>
    <p class="font-black text-gray-700 mb-1">Tidak ada hasil untuk "{{ $query }}"</p>
    <p class="text-gray-400 text-sm">Coba kata kunci yang berbeda.</p>
</div>
@endif

@endif

@endsection