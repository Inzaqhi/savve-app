@extends('layouts.kasir')
@section('title', 'Detail Transaksi')

@section('content')

    <div class="anim-fade-up delay-1 flex justify-between items-start mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #7c3aed">Transaksi</p>
            <h1 class="text-2xl font-black text-gray-900">Detail Transaksi</h1>
            <p class="text-gray-400 text-sm mt-1">Informasi lengkap transaksi penitipan barang.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('kasir.transaksi.index') }}"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition flex-shrink-0"
                style="background: white; border: 1.5px solid #e2e8f0; color: #374151"
                onmouseover="this.style.background='#f8faff'" onmouseout="this.style.background='white'">
                ← Kembali
            </a>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-5">

        {{-- Nomor Transaksi Mobile --}}
        <div class="w-full lg:hidden">
            <div class="anim-fade-up delay-2 rounded-2xl p-5 text-white relative overflow-hidden"
                style="background: linear-gradient(135deg, #1e1035, #2d1b69, #4c1d95); box-shadow: 0 8px 24px rgba(91,33,182,0.25)">
                <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #c4b5fd">Nomor Transaksi</p>
                <p class="text-xl font-black tracking-tight font-mono">{{ $transaksi->nomor_transaksi }}</p>
                <div class="mt-2">
                    <p class="text-xs uppercase tracking-wider" style="color: rgba(255,255,255,0.5)">Status</p>
                    <p class="font-bold text-white text-sm">
                        {{ $transaksi->status === 'dititip' ? 'DITITIPKAN' : ($transaksi->status === 'terlambat' ? 'TERLAMBAT' : 'SUDAH DIAMBIL') }}
                    </p>
                </div>
                <div class="absolute -bottom-4 -right-4 w-20 h-20 rounded-full" style="background: rgba(255,255,255,0.05)">
                </div>
            </div>
        </div>

        {{-- Kiri --}}
        <div class="flex-1 space-y-4">

            {{-- Info Utama --}}
            <div class="anim-fade-up delay-2 bg-white rounded-2xl border border-gray-100 overflow-hidden"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                <div class="flex justify-between items-center px-6 py-4"
                    style="background: linear-gradient(135deg, #1e1035, #2d1b69)">
                    <p class="text-xs font-bold uppercase tracking-widest" style="color: rgba(255,255,255,0.5)">Detail
                        Transaksi</p>
                    <span class="text-xs font-bold px-3 py-1 rounded-full font-mono"
                        style="background: rgba(167,139,250,0.2); color: #c4b5fd">
                        {{ $transaksi->nomor_transaksi }}
                    </span>
                </div>
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-6 mb-5 lg:mb-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: #94a3b8">Nama
                                Penitip</p>
                            <p class="text-lg font-black text-gray-800">{{ $transaksi->nama_penitip }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: #94a3b8">No.
                                WhatsApp</p>
                            <p class="text-lg font-black text-gray-800">{{ $transaksi->no_whatsapp }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: #94a3b8">Event</p>
                            <p class="text-lg font-black text-gray-800">{{ $transaksi->event->nama_event }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: #94a3b8">Kasir</p>
                            <p class="font-bold text-gray-700">{{ $transaksi->kasir->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: #94a3b8">Waktu
                                Penitipan</p>
                            <p class="font-bold text-gray-700">{{ $transaksi->waktu_penitipan->format('d M Y, H:i') }} WIB
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: #94a3b8">Metode
                                Bayar</p>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
                                style="background: {{ $transaksi->metode_bayar === 'Cash' ? '#f0fdf4' : ($transaksi->metode_bayar === 'QRIS' ? '#faf5ff' : '#eff6ff') }};
                    color: {{ $transaksi->metode_bayar === 'Cash' ? '#15803d' : ($transaksi->metode_bayar === 'QRIS' ? '#7c3aed' : '#1d4ed8') }}">
                                {{ $transaksi->metode_bayar === 'Cash' ? '💵' : ($transaksi->metode_bayar === 'QRIS' ? '📱' : '🌐') }}
                                {{ $transaksi->metode_bayar }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: #94a3b8">Status</p>
                            <span class="px-3 py-1 rounded-full text-xs font-bold"
                                style="background: {{ $transaksi->status === 'dititip' ? '#faf5ff' : ($transaksi->status === 'terlambat' ? '#fff5f5' : '#f0fdf4') }};
                    color: {{ $transaksi->status === 'dititip' ? '#7c3aed' : ($transaksi->status === 'terlambat' ? '#dc2626' : '#15803d') }}">
                                {{ $transaksi->status === 'dititip' ? 'DITITIPKAN' : ($transaksi->status === 'terlambat' ? 'TERLAMBAT' : 'SUDAH DIAMBIL') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Barang --}}
            <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 overflow-hidden"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">

                <div class="px-6 py-4" style="border-bottom: 1px solid #f5f3ff">
                    <p class="font-black text-gray-800">Daftar Barang</p>
                </div>

                <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch">
                    <table class="w-full text-sm" style="min-width: 560px">
                        <thead>
                            <tr style="background: #fdfbff; border-bottom: 1px solid #ede9fe">
                                <th class="px-5 py-3 text-left whitespace-nowrap"
                                    style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                                    Barang
                                </th>
                                <th class="px-5 py-3 text-left whitespace-nowrap"
                                    style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                                    Ukuran
                                </th>
                                <th class="px-5 py-3 text-left whitespace-nowrap"
                                    style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                                    Qty
                                </th>
                                <th class="px-5 py-3 text-right whitespace-nowrap"
                                    style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                                    Harga
                                </th>
                                <th class="px-5 py-3 text-right whitespace-nowrap"
                                    style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                                    Subtotal
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($transaksi->details as $detail)
                                <tr class="table-row" style="border-top: 1px solid #f5f3ff">
                                    <td class="px-5 py-4 font-medium text-gray-700">
                                        @foreach ($detail->jenisBarangFormatted() as $jb)
                                            <div class="mb-1 last:mb-0">
                                                <span>{{ $jb['nama'] }}</span>
                                                @if (!empty($jb['nomor_label']))
                                                    <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold"
                                                        style="background:#fef3c7;color:#92400e">#{{ $jb['nomor_label'] }}</span>
                                                @endif
                                                @if (!empty($jb['keterangan']))
                                                    <div class="text-xs text-gray-400">{{ $jb['keterangan'] }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>

                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-lg text-xs font-bold"
                                            style="background: #faf5ff; color: #7c3aed">
                                            {{ $detail->ukuran }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 font-semibold text-gray-700 whitespace-nowrap">
                                        {{ count($detail->jenis_barang ?? []) }} jenis
                                    </td>

                                    <td class="px-5 py-4 text-right text-gray-500 whitespace-nowrap">
                                        Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-bold text-gray-800 whitespace-nowrap">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr style="border-top: 2px solid #ede9fe">
                                <td colspan="4" class="px-5 py-4 text-right font-black text-gray-700 whitespace-nowrap">
                                    Total
                                </td>

                                <td class="px-5 py-4 text-right font-black text-xl whitespace-nowrap"
                                    style="color: #7c3aed">
                                    Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
            {{-- Foto Penitipan --}}
            @if ($transaksi->foto_penitipan)
                <div class="anim-fade-up delay-4 bg-white rounded-2xl border border-gray-100 overflow-hidden"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <div class="px-5 py-4" style="border-bottom: 1px solid #f5f3ff">
                        <p class="font-black text-gray-800">📷 Foto Barang (Saat Penitipan)</p>
                    </div>
                    <div class="p-4">
                        <img src="{{ asset('storage/' . $transaksi->foto_penitipan) }}" alt="Foto Barang"
                            class="w-full rounded-xl cursor-pointer"
                            style="max-height: 320px; object-fit: contain; background: #f8f5ff; border: 1.5px solid #ede9fe"
                            onclick="this.style.maxHeight = this.style.maxHeight === 'none' ? '320px' : 'none'">
                    </div>
                </div>
            @endif

            {{-- Foto Pengambilan --}}
            @if ($transaksi->foto_pengambilan)
                <div class="anim-fade-up delay-5 bg-white rounded-2xl border border-gray-100 overflow-hidden"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <div class="px-5 py-4" style="border-bottom: 1px solid #f5f3ff">
                        <p class="font-black text-gray-800">📷 Foto Pengambilan</p>
                    </div>
                    <div class="p-4">
                        <img src="{{ asset('storage/' . $transaksi->foto_pengambilan) }}" alt="Foto Pengambilan"
                            class="w-full rounded-xl cursor-pointer"
                            style="max-height: 320px; object-fit: contain; background: #f8f5ff; border: 1.5px solid #ede9fe"
                            onclick="this.style.maxHeight = this.style.maxHeight === 'none' ? '320px' : 'none'">
                    </div>
                </div>
            @endif
        </div>

        {{-- Kanan --}}
        <div class="w-full lg:w-72 flex-shrink-0 space-y-4">

            {{-- Nomor Transaksi --}}
            <div class="hidden lg:block anim-fade-up delay-2 rounded-2xl p-6 text-white relative overflow-hidden"
                style="background: linear-gradient(135deg, #1e1035, #2d1b69, #4c1d95); box-shadow: 0 8px 24px rgba(91,33,182,0.25)">
                <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color: #c4b5fd">Nomor Transaksi
                </p>
                <p class="text-2xl font-black tracking-tight leading-tight mb-4 font-mono">
                    {{ $transaksi->nomor_transaksi }}
                </p>
                <div>
                    <p class="text-xs uppercase tracking-wider" style="color: rgba(255,255,255,0.5)">Status</p>
                    <p class="font-bold text-white">
                        {{ $transaksi->status === 'dititip' ? 'DITITIPKAN' : ($transaksi->status === 'terlambat' ? 'TERLAMBAT' : 'SUDAH DIAMBIL') }}
                    </p>
                </div>
                <div class="absolute -bottom-4 -right-4 w-24 h-24 rounded-full"
                    style="background: rgba(255,255,255,0.05)"></div>
            </div>


            {{-- Aksi --}}
            <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 p-5 space-y-3"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                <p class="text-xs font-bold uppercase tracking-wider" style="color: #94a3b8">Aksi Cepat</p>

                {{-- @if (in_array($transaksi->status, ['dititip', 'terlambat']))
                    <a href="{{ route('kasir.transaksi.tambah-barang', $transaksi) }}"
                        class="w-full flex items-center justify-center gap-2 py-3 rounded-xl font-bold text-sm transition"
                        style="background: #faf5ff; color: #7c3aed; border: 1.5px solid #ede9fe">
                        ➕ Tambah Barang
                    </a>
                @endif --}}

                <a href="{{ route('kasir.transaksi.nota', $transaksi) }}" target="_blank"
                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-white font-bold text-sm transition hover:opacity-90"
                    style="background: linear-gradient(135deg, #5b21b6, #7c3aed)">
                    🖨️ Cetak Nota
                </a>

                @if (in_array($transaksi->status, ['dititip', 'terlambat']) && $transaksi->kasir_id === auth()->id())
                    <a href="{{ route('kasir.transaksi.edit', $transaksi) }}"
                        class="w-full flex items-center justify-center gap-2 py-3 rounded-xl font-bold text-sm transition hover:opacity-90"
                        style="background: #fffbeb; color: #b45309; border: 1.5px solid #fde68a">
                        ✏️ Edit Transaksi
                    </a>
                @endif

                @if (in_array($transaksi->status, ['dititip', 'terlambat']))
                    <a href="{{ route('kasir.pengambilan.index') }}"
                        class="w-full flex items-center justify-center gap-2 py-3 rounded-xl font-bold text-sm transition hover:opacity-90"
                        style="background: #f0fdf4; color: #15803d; border: 1.5px solid #bbf7d0">
                        📦 Proses Pengambilan
                    </a>
                @endif

                <a href="{{ route('kasir.transaksi.create') }}"
                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl font-bold text-sm transition"
                    style="background: #faf5ff; color: #7c3aed; border: 1.5px solid #ede9fe">
                    ➕ Transaksi Baru
                </a>
            </div>

            {{-- Timeline --}}
            <div class="anim-fade-up delay-4 bg-white rounded-2xl border border-gray-100 p-5"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color: #94a3b8">Timeline</p>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" style="background: #a78bfa"></div>
                        <div>
                            <p class="text-xs font-semibold text-gray-600">Barang Dititipkan</p>
                            <p class="text-xs text-gray-400">{{ $transaksi->waktu_penitipan->format('d M Y, H:i') }}
                                WIB
                            </p>
                        </div>
                    </div>
                    @if ($transaksi->waktu_pengambilan)
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" style="background: #22c55e"></div>
                            <div>
                                <p class="text-xs font-semibold text-gray-600">Barang Diambil</p>
                                <p class="text-xs text-gray-400">
                                    {{ $transaksi->waktu_pengambilan->format('d M Y, H:i') }}
                                    WIB</p>
                            </div>
                        </div>
                    @elseif($transaksi->status === 'terlambat')
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 animate-pulse bg-red-500"></div>
                            <div>
                                <p class="text-xs font-semibold text-red-500">Terlambat Diambil ⚠️</p>
                                <p class="text-xs text-gray-400">Event sudah berakhir, barang belum diambil</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 animate-pulse"
                                style="background: #e2e8f0"></div>
                            <div>
                                <p class="text-xs font-semibold text-gray-400">Menunggu Pengambilan...</p>
                                <p class="text-xs text-gray-300">Belum diambil</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

@endsection
