@extends('layouts.kasir')
@section('title', 'Pengambilan Barang')

@section('content')

    {{-- Header --}}
    <div class="anim-fade-up delay-1 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #7c3aed">Transaksi</p>
            <h1 class="text-xl lg:text-2xl font-black text-gray-900">Pengambilan Barang</h1>
            <p class="text-gray-400 text-sm mt-1">Scan QR code nota atau cari nama penitip untuk konfirmasi pengambilan.</p>
        </div>
    </div>

    {{-- Tab Container --}}
    <div class="anim-fade-up delay-2 bg-white rounded-2xl border border-gray-100 overflow-hidden mb-5"
        style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">

        {{-- Tab Header --}}
        <div class="flex" style="border-bottom: 2px solid #f5f3ff">
            <button onclick="switchMode('qr')" id="tab-qr"
                class="flex-1 flex items-center justify-center gap-2 py-4 text-sm font-bold transition pengambilan-tab active-pengambilan-tab">
                <span>📱</span> Scan QR Code
            </button>
            <button onclick="switchMode('nama')" id="tab-nama"
                class="flex-1 flex items-center justify-center gap-2 py-4 text-sm font-bold transition pengambilan-tab">
                <span>🔍</span> Cari Nama
            </button>
        </div>

        {{-- Panel QR --}}
        <div id="panel-qr" class="p-5 lg:p-6">
            <p class="text-sm text-center mb-5" style="color: #94a3b8">
                Arahkan kamera ke QR code pada nota penitipan
            </p>
            <div class="flex justify-center mb-5">
                <div class="relative rounded-2xl overflow-hidden flex-shrink-0"
                    style="background: #0a0a14; width: 280px; height: 280px">
                    <video id="qr-video" autoplay playsinline
                        style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transform:scaleX(1);display:block"></video>
                    <div class="absolute inset-0 pointer-events-none"
                        style="background: radial-gradient(ellipse at center, transparent 42%, rgba(0,0,0,0.7) 72%)"></div>
                    <div class="absolute pointer-events-none"
                        style="top:50%;left:50%;transform:translate(-50%,-50%);width:58%;height:58%">
                        <div
                            style="position:absolute;top:0;left:0;width:30px;height:30px;border-top:3px solid #a78bfa;border-left:3px solid #a78bfa;border-radius:6px 0 0 0">
                        </div>
                        <div
                            style="position:absolute;top:0;right:0;width:30px;height:30px;border-top:3px solid #a78bfa;border-right:3px solid #a78bfa;border-radius:0 6px 0 0">
                        </div>
                        <div
                            style="position:absolute;bottom:0;left:0;width:30px;height:30px;border-bottom:3px solid #a78bfa;border-left:3px solid #a78bfa;border-radius:0 0 0 6px">
                        </div>
                        <div
                            style="position:absolute;bottom:0;right:0;width:30px;height:30px;border-bottom:3px solid #a78bfa;border-right:3px solid #a78bfa;border-radius:0 0 6px 0">
                        </div>
                        <div id="scan-line"
                            style="position:absolute;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,#c4b5fd,#a78bfa,#c4b5fd,transparent);box-shadow:0 0 10px 2px rgba(167,139,250,0.6);animation:scanLine 1.8s ease-in-out infinite">
                        </div>
                    </div>
                    <div id="qr-status" class="absolute bottom-3 left-0 right-0 flex justify-center pointer-events-none">
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold"
                            style="background:rgba(0,0,0,0.75);color:#c4b5fd;backdrop-filter:blur(6px)">
                            📷 Kamera belum aktif
                        </span>
                    </div>
                </div>
            </div>
            <div class="max-w-xs mx-auto">
                <button onclick="toggleKameraQr()" id="btn-toggle-kamera"
                    class="w-full py-3 rounded-xl font-bold text-sm transition text-white flex items-center justify-center gap-2"
                    style="background:linear-gradient(135deg,#5b21b6,#7c3aed);box-shadow:0 4px 12px rgba(91,33,182,0.25)">
                    📷 Aktifkan Kamera
                </button>
            </div>
        </div>

        {{-- Panel Nama --}}
        <div id="panel-nama" class="p-5 lg:p-6 hidden">
            <form action="{{ route('kasir.pengambilan.cari') }}" method="POST">
                @csrf
                <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: #64748b">
                    Nama Penitip
                </label>
                <div class="flex gap-2 items-stretch">
                    <input type="text" name="nama_penitip" value="{{ isset($namaDicari) ? $namaDicari : '' }}" autofocus
                        class="flex-1 min-w-0 rounded-xl px-4 py-3 text-sm transition"
                        style="background:#faf5ff;border:1.5px solid #ede9fe;color:#374151"
                        placeholder="Contoh: Budi Santoso"
                        onfocus="this.style.borderColor='#a78bfa';this.style.boxShadow='0 0 0 3px rgba(167,139,250,0.1)'"
                        onblur="this.style.borderColor='#ede9fe';this.style.boxShadow='none'">
                    <button type="submit"
                        class="flex items-center gap-2 px-5 py-3 rounded-xl text-white font-bold text-sm transition hover:opacity-90 flex-shrink-0 whitespace-nowrap"
                        style="background:linear-gradient(135deg,#5b21b6,#7c3aed);box-shadow:0 4px 12px rgba(91,33,182,0.2)">
                        🔍 Cari
                    </button>
                </div>
            </form>
            <div class="mt-4 flex items-start gap-2.5 px-4 py-3 rounded-xl"
                style="background:#faf5ff;border:1px solid #ede9fe">
                <span class="text-sm flex-shrink-0 mt-0.5">💡</span>
                <p class="text-xs" style="color:#7c3aed">
                    Gunakan fitur ini sebagai cadangan apabila nota QR code hilang atau tidak dapat dipindai.
                </p>
            </div>
        </div>
    </div>

    {{-- Hasil QR --}}
    <div id="hasil-qr" class="hidden"></div>

    {{-- Hasil Pencarian Nama --}}
    @isset($transaksis)
        @if ($transaksis->count() > 0)
            <div class="anim-fade-up delay-3 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full animate-pulse" style="background: #a78bfa"></span>
                <p class="text-sm font-bold" style="color: #7c3aed">
                    {{ $transaksis->count() }} transaksi ditemukan untuk "{{ $namaDicari }}"
                </p>
            </div>

            <div class="anim-fade-up delay-4 bg-white rounded-2xl border border-gray-100 mb-4"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">

                {{-- indikator mobile --}}
                <div class="px-4 py-3 text-xs text-gray-400 lg:hidden" style="background:#faf5ff">
                    ← Geser tabel ke samping untuk melihat semua data →
                </div>

                {{-- wrapper scroll --}}
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-xs" style="min-width: 600px">
                        <thead>
                            <tr style="background: #fdfbff; border-bottom: 1px solid #ede9fe">
                                <th class="px-4 py-3 text-left"
                                    style="width:28%;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                                    No. Transaksi</th>
                                <th class="px-4 py-3 text-left"
                                    style="width:24%;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                                    Customer</th>
                                <th class="px-4 py-3 text-left"
                                    style="width:18%;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                                    Ukuran</th>
                                <th class="px-4 py-3 text-left"
                                    style="width:18%;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                                    Status</th>
                                <th class="px-4 py-3 text-right"
                                    style="width:12%;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaksis as $transaksi)
                                <tr class="border-t border-gray-50 hover:bg-purple-50 transition cursor-pointer"
                                    onclick="bukaDetail({{ $transaksi->id }})">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="font-bold" style="color:#7c3aed;font-family:monospace">
                                            {{ $transaksi->nomor_transaksi }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-white flex-shrink-0"
                                                style="background: linear-gradient(135deg, #5b21b6, #a78bfa); font-size: 10px">
                                                {{ strtoupper(substr($transaksi->nama_penitip, 0, 1)) }}
                                            </div>
                                            <span
                                                class="font-semibold text-gray-700">{{ Str::limit($transaksi->nama_penitip, 14) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($transaksi->details as $d)
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-bold"
                                                    style="background:#ede9fe;color:#7c3aed">{{ $d->ukuran }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 py-1 rounded-full text-xs font-bold"
                                            style="background: {{ $transaksi->status === 'terlambat' ? '#fff5f5' : '#faf5ff' }};
                                   color: {{ $transaksi->status === 'terlambat' ? '#dc2626' : '#7c3aed' }}">
                                            {{ $transaksi->status === 'terlambat' ? 'TERLAMBAT' : 'DITITIPKAN' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right" onclick="event.stopPropagation()">
                                        <button type="button"
                                            onclick="konfirmasiDariTabel({{ $transaksi->id }}, '{{ addslashes($transaksi->nama_penitip) }}', {{ $transaksi->status === 'terlambat' ? 'true' : 'false' }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-white font-bold text-xs transition hover:opacity-90"
                                            style="background: {{ $transaksi->status === 'terlambat' ? 'linear-gradient(135deg,#dc2626,#ef4444)' : 'linear-gradient(135deg,#5b21b6,#7c3aed)' }}">
                                            🛡️ Konfirmasi
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button onclick="window.location.href='{{ route('kasir.pengambilan.index') }}'"
                    class="w-full flex items-center justify-center gap-2 py-4 rounded-2xl font-bold text-sm transition mb-4"
                    style="background:white;border:1.5px solid #ede9fe;color:#7c3aed">
                    ← Cari Ulang
                </button>

                {{-- Modal Detail --}}
                @foreach ($transaksis as $transaksi)
                    <div id="modal-detail-{{ $transaksi->id }}"
                        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
                        style="background:rgba(0,0,0,0.75)"
                        onclick="if(event.target===this) tutupDetail({{ $transaksi->id }})">
                        <div class="bg-white rounded-2xl overflow-hidden overflow-y-auto"
                            style="width:600px;max-width:95vw;max-height:80vh">
                            <div class="flex justify-between items-center px-5 py-4"
                                style="background: linear-gradient(135deg, #1e1035, #2d1b69)">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-sm text-white"
                                        style="background: rgba(167,139,250,0.3)">
                                        {{ strtoupper(substr($transaksi->nama_penitip, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-white font-black text-sm leading-none">{{ $transaksi->nama_penitip }}
                                        </p>
                                        <p class="text-xs mt-0.5" style="color:#c4b5fd">{{ $transaksi->nomor_transaksi }}</p>
                                    </div>
                                </div>
                                <button onclick="tutupDetail({{ $transaksi->id }})"
                                    class="w-8 h-8 flex items-center justify-center rounded-xl text-white font-bold"
                                    style="background:rgba(255,255,255,0.15)">✕</button>
                            </div>
                            <div class="p-5">
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div class="rounded-xl p-3" style="background:#faf5ff;border:1px solid #ede9fe">
                                        <p class="text-xs font-semibold uppercase tracking-wider mb-1"
                                            style="color:#94a3b8;font-size:9px">Event</p>
                                        <p class="font-bold text-gray-800 text-sm">
                                            {{ Str::limit($transaksi->event->nama_event, 20) }}</p>
                                    </div>
                                    <div class="rounded-xl p-3" style="background:#faf5ff;border:1px solid #ede9fe">
                                        <p class="text-xs font-semibold uppercase tracking-wider mb-1"
                                            style="color:#94a3b8;font-size:9px">Waktu Titip</p>
                                        <p class="font-bold text-gray-700 text-xs">
                                            {{ $transaksi->waktu_penitipan->format('d M Y') }}</p>
                                        <p class="text-xs text-gray-400">{{ $transaksi->waktu_penitipan->format('H:i') }} WIB
                                        </p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <p class="text-xs font-bold uppercase tracking-wider mb-2" style="color:#94a3b8">Daftar
                                        Barang
                                    </p>
                                    <div class="space-y-2">
                                        @foreach ($transaksi->details as $d)
                                            <div class="flex items-center justify-between px-3 py-2 rounded-lg"
                                                style="background:#f8faff;border:1px solid #ede9fe">
                                                <span class="text-sm font-semibold text-gray-700">
                                                    {{ $d->jenis_barang_string }}
                                                </span>
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 rounded-lg text-xs font-bold"
                                                        style="background:#ede9fe;color:#7c3aed">{{ $d->ukuran }}</span>
                                                    <span class="text-xs font-bold text-gray-700">
                                                        Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @if ($transaksi->foto_penitipan)
                                    <div class="mb-4">
                                        <p class="text-xs font-bold uppercase tracking-wider mb-2" style="color:#94a3b8">📷
                                            Foto
                                            Saat Penitipan</p>
                                        <img src="{{ asset('storage/' . $transaksi->foto_penitipan) }}" alt="Foto"
                                            class="w-full max-h-40 object-cover rounded-xl cursor-pointer"
                                            style="border:1.5px solid #ede9fe" onclick="bukaFotoModal(this.src)">
                                    </div>
                                @endif
                                @if ($transaksi->status === 'terlambat')
                                    <div class="flex items-start gap-3 px-4 py-3 rounded-xl mb-4"
                                        style="background:#fff5f5;border:1.5px solid #fecaca">
                                        <span class="text-red-500 flex-shrink-0">⚠️</span>
                                        <div>
                                            <p class="text-red-700 font-bold text-sm">Pengambilan Terlambat</p>
                                            <p class="text-red-400 text-xs mt-0.5">Event sudah berakhir.</p>
                                        </div>
                                    </div>
                                @endif
                                <div class="mb-5">
                                    <p class="text-xs font-bold uppercase tracking-wider mb-2" style="color:#64748b">
                                        Foto Pengambilan
                                        <span class="font-normal normal-case" style="color:#94a3b8">(opsional)</span>
                                    </p>
                                    <div id="foto-detail-preview-{{ $transaksi->id }}" class="hidden mb-3">
                                        <div class="relative">
                                            <img id="foto-detail-img-{{ $transaksi->id }}" src="" alt="Preview"
                                                class="w-full max-h-36 object-cover rounded-xl"
                                                style="border:1.5px solid #ddd6fe">
                                            <button type="button" onclick="hapusFotoDetail({{ $transaksi->id }})"
                                                class="absolute top-2 right-2 w-7 h-7 flex items-center justify-center rounded-full text-white text-xs font-bold"
                                                style="background:rgba(0,0,0,0.6)">✕</button>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="bukaKameraDetail({{ $transaksi->id }})"
                                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm"
                                            style="background:#faf5ff;color:#7c3aed;border:1.5px solid #ede9fe">
                                            📷 Ambil Foto
                                        </button>
                                        <label
                                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm cursor-pointer"
                                            style="background:#f8faff;color:#1a3a6b;border:1.5px solid #e2e8f0">
                                            🖼️ Galeri
                                            <input type="file" accept="image/*" class="hidden"
                                                onchange="pilihFotoDetail(this, {{ $transaksi->id }})">
                                        </label>
                                    </div>
                                    <input type="hidden" id="foto-detail-input-{{ $transaksi->id }}">
                                </div>
                                <button type="button"
                                    onclick="konfirmasiDariDetail({{ $transaksi->id }}, '{{ addslashes($transaksi->nama_penitip) }}', {{ $transaksi->status === 'terlambat' ? 'true' : 'false' }})"
                                    class="w-full flex items-center justify-center gap-2 py-4 rounded-2xl text-white font-black text-sm hover:opacity-90"
                                    style="background: {{ $transaksi->status === 'terlambat' ? 'linear-gradient(135deg,#dc2626,#ef4444)' : 'linear-gradient(135deg,#5b21b6,#7c3aed)' }};
                                       box-shadow: {{ $transaksi->status === 'terlambat' ? '0 4px 12px rgba(220,38,38,0.25)' : '0 4px 12px rgba(91,33,182,0.25)' }}">
                                    {{ $transaksi->status === 'terlambat' ? '⚠️ Konfirmasi (Terlambat)' : '🛡️ Konfirmasi Pengambilan' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Kamera Detail --}}
                    <div id="modal-kamera-detail-{{ $transaksi->id }}"
                        class="hidden fixed inset-0 z-[60] flex items-center justify-center"
                        style="background:rgba(0,0,0,0.85)">
                        <div class="bg-white rounded-2xl overflow-hidden w-full max-w-sm mx-4">
                            <div class="flex justify-between items-center px-4 py-3" style="border-bottom:1px solid #f1f5f9">
                                <p class="font-black text-gray-800 text-sm">📷 Foto Pengambilan</p>
                                <button onclick="tutupKameraDetail({{ $transaksi->id }})"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg"
                                    style="background:#f1f5f9;color:#6b7280">✕</button>
                            </div>
                            <div class="p-4">
                                <div class="rounded-xl overflow-hidden mb-3"
                                    style="background:#0f0f1a;width:100%;height:260px;position:relative">
                                    <video id="video-detail-{{ $transaksi->id }}" autoplay playsinline
                                        style="width:100%;height:100%;object-fit:cover;display:block"></video>
                                </div>
                                <canvas id="canvas-detail-{{ $transaksi->id }}" class="hidden"></canvas>
                                <button type="button" onclick="jepretFotoDetail({{ $transaksi->id }})"
                                    class="w-full py-3 rounded-xl text-white font-bold text-sm"
                                    style="background:linear-gradient(135deg,#5b21b6,#7c3aed)">
                                    📸 Jepret Foto
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
        @endif
    @endisset

    {{-- Pesan Error Pencarian --}}
    @isset($errorPencarian)
        <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 overflow-hidden"
            style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
            <div class="p-10 text-center">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4"
                    style="background:#fff5f5">😕</div>
                <p class="font-black text-gray-700 text-lg mb-1">Tidak Ditemukan</p>
                <p class="text-gray-400 text-sm mb-1">{{ $errorPencarian }}</p>
                @isset($namaDicari)
                    <p class="text-xs text-gray-400 mb-4">
                        Nama yang dicari: <span class="font-bold text-gray-600">"{{ $namaDicari }}"</span>
                    </p>
                @endisset
                <button onclick="window.location.href='{{ route('kasir.pengambilan.index') }}'"
                    class="mt-2 px-6 py-2.5 rounded-xl font-bold text-sm text-white"
                    style="background:linear-gradient(135deg,#5b21b6,#7c3aed)">
                    🔍 Cari Lagi
                </button>
            </div>
        </div>
    @endisset

    {{-- Success/Error Flash --}}
    @if (session('success'))
        <div class="fixed top-4 right-4 z-[70] px-5 py-4 rounded-2xl text-white font-semibold text-sm shadow-lg"
            style="background:linear-gradient(135deg,#16a34a,#4ade80);max-width:320px" id="flash-success">
            ✅ {{ session('success') }}
        </div>
        <script>
            setTimeout(() => document.getElementById('flash-success')?.remove(), 4000)
        </script>
    @endif

    {{-- Modal Foto Fullscreen --}}
    <div id="foto-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background:rgba(0,0,0,0.92)" onclick="this.classList.add('hidden')">
        <div class="relative max-w-2xl w-full">
            <img id="foto-modal-img" src="" alt="Foto"
                class="w-full rounded-2xl object-contain max-h-screen">
            <p class="text-center text-xs text-white mt-3 opacity-60">Klik di mana saja untuk menutup</p>
        </div>
    </div>

    <style>
        @keyframes scanLine {
            0% {
                top: 0;
                opacity: 1;
            }

            50% {
                top: calc(100% - 2px);
                opacity: 0.8;
            }

            100% {
                top: 0;
                opacity: 1;
            }
        }

        .pengambilan-tab {
            color: #94a3b8;
            border-bottom: 2px solid transparent;
        }

        .active-pengambilan-tab {
            color: #5b21b6;
            border-bottom: 2px solid #7c3aed;
            background: #faf5ff;
        }

        #qr-video,
        video[autoplay] {
            transform: scaleX(1) !important;
            -webkit-transform: scaleX(1) !important;
        }
    </style>

    <script src="{{ asset('js/jsqr.min.js') }}"></script>
    <script>
        // ── Tab Switch ──
        function switchMode(mode) {
            ['qr', 'nama'].forEach(m => {
                document.getElementById('panel-' + m).classList.add('hidden');
                document.getElementById('tab-' + m).classList.remove('active-pengambilan-tab');
            });
            document.getElementById('panel-' + mode).classList.remove('hidden');
            document.getElementById('tab-' + mode).classList.add('active-pengambilan-tab');
            if (mode !== 'qr') stopQrScanner();
        }

        // Auto buka tab nama jika ada hasil atau error pencarian
        @isset($transaksis)
            switchMode('nama');
        @endisset
        @isset($errorPencarian)
            switchMode('nama');
        @endisset

        // ── QR Scanner ──
        let qrStream = null,
            isScanning = false;

        function setQrStatus(text, color) {
            document.getElementById('qr-status').innerHTML =
                `<span class="px-3 py-1.5 rounded-full text-xs font-bold" style="background:rgba(0,0,0,0.75);color:${color};backdrop-filter:blur(6px)">${text}</span>`;
        }

        async function toggleKameraQr() {
            const btn = document.getElementById('btn-toggle-kamera');
            if (qrStream) {
                stopQrScanner();
                btn.innerHTML = '📷 Aktifkan Kamera';
                btn.style.background = 'linear-gradient(135deg,#5b21b6,#7c3aed)';
                setQrStatus('📷 Kamera belum aktif', '#c4b5fd');
            } else {
                btn.innerHTML = '⏳ Memuat kamera...';
                btn.disabled = true;
                await startQrScanner();
                btn.disabled = false;
                btn.innerHTML = '⏹ Hentikan Kamera';
                btn.style.background = '#dc2626';
            }
        }

        async function startQrScanner() {
            const constraints = [{
                    video: {
                        facingMode: {
                            exact: 'environment'
                        },
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        }
                    }
                },
                {
                    video: {
                        facingMode: 'environment'
                    }
                },
                {
                    video: true
                }
            ];
            for (const c of constraints) {
                try {
                    qrStream = await navigator.mediaDevices.getUserMedia(c);
                    break;
                } catch (e) {
                    continue;
                }
            }
            if (!qrStream) {
                alert('Tidak bisa mengakses kamera.');
                return;
            }

            const video = document.getElementById('qr-video');
            video.srcObject = qrStream;
            await new Promise(resolve => {
                video.onloadedmetadata = () => {
                    video.play();
                    resolve();
                };
            });

            isScanning = true;
            setQrStatus('🔍 Scanning...', '#c4b5fd');
            scanLoop();
        }

        function stopQrScanner() {
            isScanning = false;
            qrStream?.getTracks().forEach(t => t.stop());
            qrStream = null;
        }

        const _scanCanvas = document.createElement('canvas');
        const _scanCtx = _scanCanvas.getContext('2d', {
            willReadFrequently: true
        });

        function scanLoop() {
            if (!isScanning) return;
            const video = document.getElementById('qr-video');

            if (video && video.readyState >= 2 && video.videoWidth > 0) {
                const w = video.videoWidth,
                    h = video.videoHeight;
                _scanCanvas.width = w;
                _scanCanvas.height = h;
                _scanCtx.drawImage(video, 0, 0, w, h);

                // Coba full frame dulu
                let code = jsQR(
                    _scanCtx.getImageData(0, 0, w, h).data, w, h, {
                        inversionAttempts: 'attemptBoth'
                    }
                );

                // Kalau gagal, coba crop tengah 70% (area viewfinder)
                if (!code) {
                    const size = Math.min(w, h) * 0.7;
                    const cx = (w - size) / 2;
                    const cy = (h - size) / 2;
                    _scanCtx.drawImage(video, cx, cy, size, size, 0, 0, size, size);
                    code = jsQR(
                        _scanCtx.getImageData(0, 0, size, size).data, size, size, {
                            inversionAttempts: 'attemptBoth'
                        }
                    );
                }

                if (code?.data?.trim()) {
                    isScanning = false;
                    stopQrScanner();
                    setQrStatus('✅ QR Terdeteksi!', '#4ade80');
                    prosesQr(code.data.trim());
                    return;
                }
            }

            // Interval 150ms — cukup responsif tanpa membebani CPU
            setTimeout(scanLoop, 150);
        }


        function scanQrFrame() {
            const video = document.getElementById('qr-video');
            if (!video || video.readyState < 2 || !video.videoWidth) return;
            const w = video.videoWidth,
                h = video.videoHeight;
            const canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d', {
                willReadFrequently: true
            });
            ctx.drawImage(video, 0, 0, w, h);
            for (const attempt of ['dontInvert', 'onlyInvert', 'attemptBoth']) {
                const code = jsQR(ctx.getImageData(0, 0, w, h).data, w, h, {
                    inversionAttempts: attempt
                });
                if (code?.data?.trim()) {
                    handleQrFound(code.data.trim());
                    return;
                }
            }
            const size = Math.min(w, h) * 0.7,
                cx = (w - size) / 2,
                cy = (h - size) / 2;
            const c2 = document.createElement('canvas');
            c2.width = size;
            c2.height = size;
            c2.getContext('2d', {
                willReadFrequently: true
            }).drawImage(video, cx, cy, size, size, 0, 0, size, size);
            for (const attempt of ['dontInvert', 'onlyInvert', 'attemptBoth']) {
                const code = jsQR(c2.getContext('2d').getImageData(0, 0, size, size).data, size, size, {
                    inversionAttempts: attempt
                });
                if (code?.data?.trim()) {
                    handleQrFound(code.data.trim());
                    return;
                }
            }
        }

        function handleQrFound(value) {
            isScanning = false;
            stopQrScanner();
            setQrStatus('✅ QR Terdeteksi!', '#4ade80');
            prosesQr(value);
        }

        async function prosesQr(nomorTransaksi) {
            setQrStatus('⏳ Memproses...', '#fbbf24');
            try {
                const res = await fetch('{{ route('kasir.pengambilan.scan-qr') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    // Kirim nomor_transaksi, bukan nama_penitip
                    body: JSON.stringify({
                        nomor_transaksi: nomorTransaksi
                    })
                });
                if (!res.ok) throw new Error('Server error: ' + res.status);
                const data = await res.json();
                const hasil = document.getElementById('hasil-qr');
                if (!data.found) {
                    setQrStatus('❌ Tidak ditemukan', '#f87171');
                    hasil.innerHTML = `
                        <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center mb-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.04)">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4" style="background:#fff5f5">😕</div>
                            <p class="font-black text-gray-700 text-lg mb-1">Tidak Ditemukan</p>
                            <p class="text-gray-400 text-sm mb-4">Nama: <span class="font-bold text-gray-600">${namaPenitip}</span><br>Mungkin sudah diambil atau tidak ada transaksi aktif.</p>
                            <button onclick="resetQr()" class="px-6 py-2.5 rounded-xl text-white font-bold text-sm" style="background:linear-gradient(135deg,#5b21b6,#7c3aed)">🔄 Scan Ulang</button>
                        </div>`;
                    hasil.classList.remove('hidden');
                    return;
                }
                const t = data.transaksi,
                    isTerlambat = t.status === 'terlambat';
                hasil.innerHTML = `
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-4" style="box-shadow:0 2px 16px rgba(0,0,0,0.06)">
                        <div class="flex justify-between items-center px-5 py-4" style="background:linear-gradient(135deg,#1e1035,#2d1b69)">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-sm text-white" style="background:rgba(167,139,250,0.3)">${t.nama_penitip.charAt(0).toUpperCase()}</div>
                                <div>
                                    <p class="text-white font-black text-sm leading-none">${t.nama_penitip}</p>
                                    <p class="text-xs mt-0.5" style="color:#c4b5fd">✅ QR Terdeteksi</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold px-3 py-1.5 rounded-full font-mono" style="background:rgba(167,139,250,0.2);color:#c4b5fd">${t.nomor}</span>
                        </div>
                        <div class="p-5">
                            ${t.total_transaksi_aktif > 1 ? `<div class="flex items-start gap-3 px-4 py-3 rounded-xl mb-4" style="background:#fffbeb;border:1.5px solid #fde68a"><span>⚠️</span><div><p class="font-bold text-sm" style="color:#92400e">${t.total_transaksi_aktif} transaksi aktif ditemukan</p><p class="text-xs mt-0.5" style="color:#b45309">Menampilkan transaksi terbaru.</p></div></div>` : ''}
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="rounded-xl p-3" style="background:#faf5ff;border:1px solid #ede9fe"><p class="text-xs font-semibold uppercase mb-1" style="color:#94a3b8;font-size:9px">Event</p><p class="font-bold text-gray-800 text-sm">${t.event}</p></div>
                                <div class="rounded-xl p-3" style="background:#faf5ff;border:1px solid #ede9fe"><p class="text-xs font-semibold uppercase mb-1" style="color:#94a3b8;font-size:9px">Waktu Titip</p><p class="font-bold text-gray-700 text-xs">${t.waktu_penitipan}</p></div>
                                <div class="rounded-xl p-3" style="background:#faf5ff;border:1px solid #ede9fe"><p class="text-xs font-semibold uppercase mb-1" style="color:#94a3b8;font-size:9px">Total Barang</p><p class="font-black text-gray-800 text-lg">${t.total_barang} <span style="font-size:11px;font-weight:400;color:#94a3b8">unit</span></p></div>
                                <div class="rounded-xl p-3" style="background:#faf5ff;border:1px solid #ede9fe"><p class="text-xs font-semibold uppercase mb-1" style="color:#94a3b8;font-size:9px">Status</p><p class="font-bold text-sm" style="color:${isTerlambat ? '#dc2626' : '#7c3aed'}">${isTerlambat ? 'Terlambat' : 'Dititipkan'}</p></div>
                            </div>
                            ${t.details?.length ? `<div class="mb-4"><p class="text-xs font-bold uppercase mb-2" style="color:#94a3b8">Daftar Barang</p><div class="space-y-2">${t.details.map(d => `<div class="flex items-center justify-between px-3 py-2 rounded-lg" style="background:#f8faff;border:1px solid #ede9fe"><span class="text-sm font-semibold text-gray-700">${d.nama}</span><div class="flex items-center gap-2"><span class="px-2 py-0.5 rounded-lg text-xs font-bold" style="background:#ede9fe;color:#7c3aed">${d.ukuran}</span><span class="text-xs font-bold text-gray-700">${d.subtotal}</span></div></div>`).join('')}</div></div>` : ''}
                            ${t.foto_penitipan ? `<div class="mb-4"><p class="text-xs font-bold uppercase mb-2" style="color:#94a3b8">📷 Foto Penitipan</p><img src="${t.foto_penitipan}" class="w-full max-h-48 object-cover rounded-xl cursor-pointer" style="border:1.5px solid #ede9fe" onclick="bukaFotoModal(this.src)"></div>` : ''}
                            ${isTerlambat ? `<div class="flex items-start gap-3 px-4 py-3 rounded-xl mb-4" style="background:#fff5f5;border:1.5px solid #fecaca"><span style="color:#ef4444">⚠️</span><div><p class="font-bold text-sm" style="color:#dc2626">Pengambilan Terlambat</p><p class="text-xs mt-0.5" style="color:#f87171">Event sudah berakhir.</p></div></div>` : ''}
                            <div class="mb-5">
                                <p class="text-xs font-bold uppercase mb-2" style="color:#64748b">Foto Pengambilan <span style="font-weight:400;text-transform:none;color:#94a3b8">(opsional)</span></p>
                                <div id="foto-qr-preview" class="hidden mb-3"><div style="position:relative"><img id="foto-qr-img" src="" class="w-full max-h-40 object-cover rounded-xl" style="border:1.5px solid #ddd6fe"><button onclick="hapusFotoQr()" type="button" class="absolute top-2 right-2 w-7 h-7 flex items-center justify-center rounded-full text-white text-xs font-bold" style="background:rgba(0,0,0,0.6)">✕</button></div></div>
                                <button onclick="bukaKameraQrAmbil()" type="button" class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm" style="background:#faf5ff;color:#7c3aed;border:1.5px solid #ede9fe">📷 Ambil Foto</button>
                                <input type="hidden" id="foto-qr-input">
                            </div>
                            <button onclick="konfirmasiQr(${t.id}, ${isTerlambat})" class="w-full py-4 rounded-2xl text-white font-black text-sm hover:opacity-90 flex items-center justify-center gap-2 mb-2" style="background:${isTerlambat ? 'linear-gradient(135deg,#dc2626,#ef4444)' : 'linear-gradient(135deg,#5b21b6,#7c3aed)'}">${isTerlambat ? '⚠️ Konfirmasi (Terlambat)' : '🛡️ Konfirmasi Pengambilan'}</button>
                            <button onclick="resetQr()" class="w-full py-3 rounded-xl font-bold text-sm" style="background:white;border:1.5px solid #ede9fe;color:#7c3aed">🔄 Scan Ulang</button>
                        </div>
                    </div>`;
                hasil.classList.remove('hidden');
                hasil.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            } catch (err) {
                setQrStatus('❌ Error: ' + err.message, '#f87171');
            }
        }

        function resetQr() {
            isScanning = false;
            const hasil = document.getElementById('hasil-qr');
            hasil.innerHTML = '';
            hasil.classList.add('hidden');
            const btn = document.getElementById('btn-toggle-kamera');
            btn.innerHTML = '📷 Aktifkan Kamera';
            btn.style.background = 'linear-gradient(135deg,#5b21b6,#7c3aed)';
            setQrStatus('📷 Kamera belum aktif', '#c4b5fd');
        }

        async function konfirmasiQr(transaksiId, isTerlambat) {
            const msg = isTerlambat ? 'Barang ini terlambat diambil. Tetap konfirmasi?' :
                'Konfirmasi pengambilan barang ini?';
            if (!confirm(msg)) return;
            try {
                await fetch(`/kasir/pengambilan/konfirmasi/${transaksiId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: new URLSearchParams({
                        '_token': '{{ csrf_token() }}',
                        'foto_pengambilan': document.getElementById('foto-qr-input')?.value || ''
                    })
                });
                window.location.href = '{{ route('kasir.pengambilan.index') }}';
            } catch (err) {
                alert('Gagal konfirmasi: ' + err.message);
            }
        }

        // ── Kamera QR Ambil ──
        let streamQrAmbil = null;

        function bukaKameraQrAmbil() {
            const modal = document.createElement('div');
            modal.id = 'modal-qr-ambil';
            modal.style.cssText =
                'position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.85)';
            modal.innerHTML =
                `<div class="bg-white rounded-2xl overflow-hidden w-full max-w-sm mx-4"><div class="flex justify-between items-center px-4 py-3" style="border-bottom:1px solid #f1f5f9"><p class="font-black text-gray-800 text-sm">📷 Foto Pengambilan</p><button onclick="tutupModalQrAmbil()" class="w-8 h-8 flex items-center justify-center rounded-lg" style="background:#f1f5f9;color:#6b7280">✕</button></div><div class="p-4"><div class="rounded-xl overflow-hidden mb-3" style="background:#0f0f1a;width:100%;height:260px;position:relative"><video id="video-qr-ambil" autoplay playsinline style="width:100%;height:100%;object-fit:cover;display:block"></video></div><canvas id="canvas-qr-ambil" class="hidden"></canvas><button onclick="jepretQrAmbil()" type="button" class="w-full py-3 rounded-xl text-white font-bold text-sm" style="background:linear-gradient(135deg,#5b21b6,#7c3aed)">📸 Jepret Foto</button></div></div>`;
            document.body.appendChild(modal);
            (async () => {
                for (const c of [{
                        video: {
                            facingMode: {
                                ideal: 'environment'
                            }
                        }
                    }, {
                        video: true
                    }]) {
                    try {
                        streamQrAmbil = await navigator.mediaDevices.getUserMedia(c);
                        break;
                    } catch (e) {
                        continue;
                    }
                }
                if (streamQrAmbil) document.getElementById('video-qr-ambil').srcObject = streamQrAmbil;
                else {
                    alert('Tidak bisa mengakses kamera.');
                    tutupModalQrAmbil();
                }
            })();
        }

        function tutupModalQrAmbil() {
            streamQrAmbil?.getTracks().forEach(t => t.stop());
            streamQrAmbil = null;
            document.getElementById('modal-qr-ambil')?.remove();
        }

        function jepretQrAmbil() {
            const video = document.getElementById('video-qr-ambil');
            const canvas = document.getElementById('canvas-qr-ambil');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            document.getElementById('foto-qr-input').value = dataUrl;
            document.getElementById('foto-qr-img').src = dataUrl;
            document.getElementById('foto-qr-preview').classList.remove('hidden');
            tutupModalQrAmbil();
        }

        function hapusFotoQr() {
            document.getElementById('foto-qr-input').value = '';
            document.getElementById('foto-qr-preview').classList.add('hidden');
        }

        // ── Foto Modal ──
        function bukaFotoModal(src) {
            document.getElementById('foto-modal-img').src = src;
            document.getElementById('foto-modal').classList.remove('hidden');
        }

        // ── Modal Detail ──
        function bukaDetail(id) {
            document.getElementById('modal-detail-' + id).classList.remove('hidden');
        }

        function tutupDetail(id) {
            document.getElementById('modal-detail-' + id).classList.add('hidden');
        }

        function konfirmasiDariTabel(id, nama, isTerlambat) {
            const msg = isTerlambat ? 'Barang ini terlambat diambil. Tetap konfirmasi?' :
                `Konfirmasi pengambilan barang atas nama ${nama}?`;
            if (!confirm(msg)) return;
            bukaDetail(id);
        }

        async function konfirmasiDariDetail(id, nama, isTerlambat) {
            const foto = document.getElementById('foto-detail-input-' + id)?.value || '';
            try {
                await fetch(`/kasir/pengambilan/konfirmasi/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: new URLSearchParams({
                        '_token': '{{ csrf_token() }}',
                        'foto_pengambilan': foto
                    })
                });
                window.location.href = '{{ route('kasir.pengambilan.index') }}';
            } catch (err) {
                alert('Gagal konfirmasi: ' + err.message);
            }
        }

        // ── Kamera Detail Popup ──
        let streamsDetail = {};
        async function bukaKameraDetail(id) {
            document.getElementById('modal-kamera-detail-' + id).classList.remove('hidden');
            for (const c of [{
                    video: {
                        facingMode: {
                            ideal: 'environment'
                        }
                    }
                }, {
                    video: true
                }]) {
                try {
                    streamsDetail[id] = await navigator.mediaDevices.getUserMedia(c);
                    break;
                } catch (e) {
                    continue;
                }
            }
            if (streamsDetail[id]) document.getElementById('video-detail-' + id).srcObject = streamsDetail[id];
            else {
                alert('Tidak bisa mengakses kamera.');
                tutupKameraDetail(id);
            }
        }

        function tutupKameraDetail(id) {
            streamsDetail[id]?.getTracks().forEach(t => t.stop());
            delete streamsDetail[id];
            document.getElementById('modal-kamera-detail-' + id).classList.add('hidden');
        }

        function jepretFotoDetail(id) {
            const video = document.getElementById('video-detail-' + id);
            const canvas = document.getElementById('canvas-detail-' + id);
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            document.getElementById('foto-detail-input-' + id).value = dataUrl;
            document.getElementById('foto-detail-img-' + id).src = dataUrl;
            document.getElementById('foto-detail-preview-' + id).classList.remove('hidden');
            tutupKameraDetail(id);
        }

        function pilihFotoDetail(input, id) {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('foto-detail-input-' + id).value = e.target.result;
                document.getElementById('foto-detail-img-' + id).src = e.target.result;
                document.getElementById('foto-detail-preview-' + id).classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        function hapusFotoDetail(id) {
            document.getElementById('foto-detail-input-' + id).value = '';
            document.getElementById('foto-detail-preview-' + id).classList.add('hidden');
        }
    </script>

@endsection
