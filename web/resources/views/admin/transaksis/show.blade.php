@extends('layouts.admin')
@section('title', 'Detail Transaksi')

@section('content')

    <div class="anim-fade-up delay-1 flex justify-between items-start mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #4a9eff">Transaksi</p>
            <h1 class="text-2xl font-black text-gray-900">Detail Transaksi</h1>
            <p class="text-gray-400 text-sm mt-1">Informasi lengkap transaksi penitipan barang.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.transaksis.index') }}"
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
                style="background: linear-gradient(135deg, #091629, #0c1e3d, #0f2044); box-shadow: 0 8px 24px rgba(15,32,68,0.25)">
                <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #4a9eff">Nomor Transaksi</p>
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
                    style="background: linear-gradient(135deg, #0f2044, #1e4d8c)">
                    <p class="text-xs font-bold uppercase tracking-widest" style="color: rgba(255,255,255,0.5)">Detail
                        Transaksi</p>
                    <span class="text-xs font-bold px-3 py-1 rounded-full font-mono"
                        style="background: rgba(74,158,255,0.2); color: #cfe6ff">
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
                                style="background: {{ $transaksi->metode_bayar === 'Cash' ? '#f0fdf4' : ($transaksi->metode_bayar === 'QRIS' ? '#f8faff' : '#eff6ff') }};
                    color: {{ $transaksi->metode_bayar === 'Cash' ? '#15803d' : ($transaksi->metode_bayar === 'QRIS' ? '#1e4d8c' : '#1d4ed8') }}">
                                {{ $transaksi->metode_bayar === 'Cash' ? '💵' : ($transaksi->metode_bayar === 'QRIS' ? '📱' : '🌐') }}
                                {{ $transaksi->metode_bayar }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: #94a3b8">Status</p>
                            <span class="px-3 py-1 rounded-full text-xs font-bold"
                                style="background: {{ $transaksi->status === 'dititip' ? '#f8faff' : ($transaksi->status === 'terlambat' ? '#fff5f5' : '#f0fdf4') }};
                    color: {{ $transaksi->status === 'dititip' ? '#1e4d8c' : ($transaksi->status === 'terlambat' ? '#dc2626' : '#15803d') }}">
                                {{ $transaksi->status === 'dititip' ? 'DITITIPKAN' : ($transaksi->status === 'terlambat' ? 'TERLAMBAT' : 'SUDAH DIAMBIL') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Barang --}}
            <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 overflow-hidden"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">

                <div class="px-6 py-4" style="border-bottom: 1px solid #f1f5f9">
                    <p class="font-black text-gray-800">Daftar Barang</p>
                </div>

                <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch">
                    <table class="w-full text-sm" style="min-width: 560px">
                        <thead>
                            <tr style="background: #f8faff; border-bottom: 1px solid #e8edf5">
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
                                <tr class="table-row" style="border-top: 1px solid #f1f5f9">
                                    <td class="px-5 py-4 font-medium text-gray-700 whitespace-nowrap">
                                        {{ $detail->jenis_barang_string }}
                                    </td>

                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-lg text-xs font-bold"
                                            style="background: #f8faff; color: #1e4d8c">
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
                            <tr style="border-top: 2px solid #e8edf5">
                                <td colspan="4" class="px-5 py-4 text-right font-black text-gray-700 whitespace-nowrap">
                                    Total
                                </td>

                                <td class="px-5 py-4 text-right font-black text-xl whitespace-nowrap"
                                    style="color: #1e4d8c">
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
                    <div class="px-5 py-4" style="border-bottom: 1px solid #f1f5f9">
                        <p class="font-black text-gray-800">📷 Foto Barang (Saat Penitipan)</p>
                    </div>
                    <div class="p-4">
                        <img src="{{ asset('storage/' . $transaksi->foto_penitipan) }}" alt="Foto Barang"
                            class="w-full rounded-xl cursor-pointer"
                            style="max-height: 320px; object-fit: contain; background: #f8faff; border: 1.5px solid #e8edf5"
                            onclick="this.style.maxHeight = this.style.maxHeight === 'none' ? '320px' : 'none'">
                    </div>
                </div>
            @endif

            {{-- Foto Pengambilan --}}
            @if ($transaksi->foto_pengambilan)
                <div class="anim-fade-up delay-5 bg-white rounded-2xl border border-gray-100 overflow-hidden"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <div class="px-5 py-4" style="border-bottom: 1px solid #f1f5f9">
                        <p class="font-black text-gray-800">📷 Foto Pengambilan</p>
                    </div>
                    <div class="p-4">
                        <img src="{{ asset('storage/' . $transaksi->foto_pengambilan) }}" alt="Foto Pengambilan"
                            class="w-full rounded-xl cursor-pointer"
                            style="max-height: 320px; object-fit: contain; background: #f8faff; border: 1.5px solid #e8edf5"
                            onclick="this.style.maxHeight = this.style.maxHeight === 'none' ? '320px' : 'none'">
                    </div>
                </div>
            @endif
        </div>

        {{-- Kanan --}}
        <div class="w-full lg:w-72 flex-shrink-0 space-y-4">

            {{-- Nomor Transaksi --}}
            <div class="hidden lg:block anim-fade-up delay-2 rounded-2xl p-6 text-white relative overflow-hidden"
                style="background: linear-gradient(135deg, #091629, #0c1e3d, #0f2044); box-shadow: 0 8px 24px rgba(15,32,68,0.25)">
                <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color: #4a9eff">Nomor Transaksi
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

                <a href="{{ route('admin.transaksis.index') }}"
                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-white font-bold text-sm transition hover:opacity-90"
                    style="background: linear-gradient(135deg, #0f2044, #1e4d8c)">
                    📋 Lihat Semua Transaksi
                </a>

                <button type="button" onclick="bukaModalHapus()"
                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl font-bold text-sm transition hover:opacity-90"
                    style="background: #fff5f5; color: #dc2626; border: 1.5px solid #fecaca">
                    🗑️ Hapus Transaksi
                </button>
            </div>

            {{-- Timeline --}}
            <div class="anim-fade-up delay-4 bg-white rounded-2xl border border-gray-100 p-5"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color: #94a3b8">Timeline</p>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" style="background: #4a9eff"></div>
                        <div>
                            <p class="text-xs font-semibold text-gray-600">Barang Dititipkan</p>
                            <p class="text-xs text-gray-400">{{ $transaksi->waktu_penitipan->format('d M Y, H:i') }}
                                WIB
                            </p>
                        </div>
                    </div>
                    @if ($transaksi->waktu_pengambilan)
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" style="background: #34d399"></div>
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

    {{-- Modal Konfirmasi Hapus --}}
    <div id="modal-hapus" class="hidden fixed inset-0 z-50"
        style="background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; backdrop-filter: blur(2px);">

        <div class="anim-scale-in"
            style="background: white; border-radius: 20px; width: 100%; max-width: 380px; margin: 0 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">

            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4"
                    style="background: #fff5f5">
                    🗑️
                </div>
                <h3 class="font-black text-gray-900 text-lg mb-1">Hapus Transaksi Ini?</h3>
                <p class="text-sm text-gray-400">
                    Transaksi <span class="font-bold font-mono text-gray-700">{{ $transaksi->nomor_transaksi }}</span>
                    milik <span class="font-bold text-gray-700">{{ $transaksi->nama_penitip }}</span>
                    akan dihapus permanen beserta semua detail barangnya. Aksi ini tidak dapat dibatalkan.
                </p>
            </div>

            {{-- Tombol --}}
            <div class="px-6 pb-6 flex gap-3">
                <button type="button" onclick="tutupModalHapus()"
                    class="flex-1 py-3 rounded-xl font-bold text-sm transition"
                    style="background: #f1f5f9; color: #64748b">
                    Batal
                </button>
                <form action="{{ route('admin.transaksis.destroy', $transaksi) }}" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full py-3 rounded-xl text-white font-bold text-sm transition hover:opacity-90"
                        style="background: linear-gradient(135deg, #dc2626, #ef4444)">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function bukaModalHapus() {
                const modal = document.getElementById('modal-hapus');
                modal.style.display = 'flex';
                modal.classList.remove('hidden');
            }

            function tutupModalHapus() {
                const modal = document.getElementById('modal-hapus');
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }

            // Tutup modal kalau klik area gelap di luar box
            document.getElementById('modal-hapus').addEventListener('click', function(e) {
                if (e.target === this) tutupModalHapus();
            });
        </script>
    @endpush

@endsection
