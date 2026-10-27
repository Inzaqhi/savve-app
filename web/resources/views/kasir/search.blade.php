@extends('layouts.kasir')
@section('title', 'Hasil Pencarian')

@section('content')

<div class="anim-fade-up delay-1 mb-6">
    <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #7c3aed">Pencarian</p>
    <h1 class="text-xl lg:text-2xl font-black text-gray-900">
        Hasil untuk "{{ $query }}"
    </h1>
    <p class="text-gray-400 text-sm mt-1">{{ $transaksis->count() }} transaksi ditemukan</p>
</div>

{{-- Search Bar --}}
<div class="anim-fade-up delay-2 bg-white rounded-2xl border border-gray-100 p-4 mb-6"
    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
    <form method="GET" action="{{ route('kasir.search') }}">
        <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
            <input type="text" name="q" value="{{ $query }}" autofocus
                class="w-full rounded-xl pl-11 pr-4 py-3.5 text-sm transition"
                style="background: #faf5ff; border: 1.5px solid #ede9fe; color: #374151"
                placeholder="Cari transaksi atau nama penitip..."
                onfocus="this.style.borderColor='#a78bfa'; this.style.boxShadow='0 0 0 3px rgba(167,139,250,0.1)'"
                onblur="this.style.borderColor='#ede9fe'; this.style.boxShadow='none'">
        </div>
    </form>
</div>

@if(strlen($query) < 2)
<div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 p-12 text-center"
    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
    <div class="text-4xl mb-3">🔍</div>
    <p class="font-semibold text-gray-400">Ketik minimal 2 karakter untuk mencari.</p>
</div>
@elseif($transaksis->count() === 0)
<div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 p-12 text-center"
    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
    <div class="text-4xl mb-3">😕</div>
    <p class="font-black text-gray-700 mb-1">Tidak ada hasil untuk "{{ $query }}"</p>
    <p class="text-gray-400 text-sm">Coba kata kunci yang berbeda.</p>
</div>
@else
<div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 overflow-hidden"
    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
        <span class="text-base">📋</span>
        <p class="font-black text-gray-800">Transaksi Kamu</p>
        <span class="ml-auto text-xs font-bold px-2 py-1 rounded-full"
            style="background: #faf5ff; color: #7c3aed">{{ $transaksis->count() }} hasil</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" style="min-width: 500px">
            <thead>
                <tr style="background: #fdfbff; border-bottom: 1px solid #ede9fe">
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
                <tr class="table-row" style="border-top: 1px solid #f5f3ff">
                    <td class="px-5 py-3 whitespace-nowrap">
                        <a href="{{ route('kasir.transaksi.show', $t) }}"
                            class="font-bold hover:underline font-mono"
                            style="color: #7c3aed; font-size: 12px">
                            {{ $t->nomor_transaksi }}
                        </a>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-white flex-shrink-0"
                                style="background: linear-gradient(135deg, #5b21b6, #a78bfa); font-size: 10px">
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
                            style="background: {{ $t->status === 'dititip' ? '#faf5ff' : '#f0fdf4' }};
                                   color: {{ $t->status === 'dititip' ? '#7c3aed' : '#15803d' }}">
                            {{ $t->status === 'dititip' ? 'DITITIPKAN' : 'DIAMBIL' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-bold whitespace-nowrap" style="color: #5b21b6">
                        Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3">
                        <a href="{{ route('kasir.transaksi.show', $t) }}"
                            class="text-gray-300 hover:text-purple-400 text-lg">⋯</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection