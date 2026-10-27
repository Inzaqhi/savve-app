<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savve — Pilih Event</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">
    <style>
        * {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .anim-fade-up {
            animation: fadeUp 0.5s ease forwards;
        }

        .delay-1 {
            animation-delay: 0.05s;
            opacity: 0;
        }

        .delay-2 {
            animation-delay: 0.10s;
            opacity: 0;
        }

        .delay-3 {
            animation-delay: 0.15s;
            opacity: 0;
        }

        .delay-4 {
            animation-delay: 0.20s;
            opacity: 0;
        }
    </style>
</head>

<body style="background: #f5f3ff; min-height: 100vh;">

    {{-- Topbar minimal --}}
    <div class="sticky top-0 z-10 bg-white border-b"
        style="border-color: #ede9fe; box-shadow: 0 1px 8px rgba(0,0,0,0.04)">
        <div class="max-w-2xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/Logo.png') }}" alt="Savve" class="h-8 w-auto object-contain">
                <div>
                    <p class="text-xs font-bold leading-none" style="color: #1e1035">Vendor Savve</p>
                    <p class="text-xs mt-0.5" style="color: #a78bfa">Kasir Panel</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold leading-none text-gray-700">{{ auth()->user()->name }}</p>
                    <p class="text-xs mt-0.5" style="color: #a78bfa">Kasir</p>
                </div>
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-sm text-white flex-shrink-0"
                    style="background: linear-gradient(135deg, #5b21b6, #a78bfa)">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg transition"
                        style="background: #fff5f5; color: #ef4444; border: 1px solid #fecaca">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-2xl mx-auto px-4 py-8">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="anim-fade-up delay-1 mb-5 flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold"
                style="background: #faf5ff; border: 1.5px solid #ddd6fe; color: #7c3aed">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if (session('info'))
            <div class="anim-fade-up delay-1 mb-5 flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold"
                style="background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1d4ed8">
                ℹ {{ session('info') }}
            </div>
        @endif
        @if (session('warning'))
            <div class="anim-fade-up delay-1 mb-5 flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold"
                style="background: #fffbeb; border: 1.5px solid #fde68a; color: #92400e">
                ⚠ {{ session('warning') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="anim-fade-up delay-1 mb-6">
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #7c3aed">Setup</p>
            <h1 class="text-2xl font-black text-gray-900">Pilih Event</h1>
            <p class="text-gray-400 text-sm mt-1">Pilih event aktif sebelum memulai transaksi penitipan.</p>
        </div>

        {{-- Event Aktif Saat Ini --}}
        @if ($eventAktif)
            <div class="anim-fade-up delay-2 rounded-2xl p-5 mb-5 text-white"
                style="background: linear-gradient(135deg, #1e1035, #2d1b69); box-shadow: 0 8px 24px rgba(91,33,182,0.2)">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #c4b5fd">
                            Event Aktif Saat Ini
                        </p>
                        <p class="text-xl font-black truncate">{{ $eventAktif->nama_event }}</p>
                        <p class="text-sm mt-1" style="color: #c4b5fd">
                            Kode: <span class="font-mono font-bold">{{ $eventAktif->kode_event }}</span>
                            · {{ $eventAktif->tanggal_mulai->format('d M') }} –
                            {{ $eventAktif->tanggal_selesai->format('d M Y') }}
                        </p>
                    </div>
                    <form action="{{ route('kasir.event.ganti') }}" method="POST" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-xl font-bold text-sm transition"
                            style="background: rgba(255,255,255,0.15); color: white"
                            onclick="return confirm('Ganti event? Kamu perlu memilih event baru.')">
                            🔄 Ganti
                        </button>
                    </form>
                </div>
            </div>

            <div class="anim-fade-up delay-3 flex gap-3 mb-6">
                <a href="{{ route('kasir.dashboard') }}"
                    class="flex-1 flex items-center justify-center gap-2 py-3.5 rounded-xl text-white font-bold text-sm transition hover:opacity-90"
                    style="background: linear-gradient(135deg, #5b21b6, #7c3aed); box-shadow: 0 4px 12px rgba(91,33,182,0.25)">
                    ✅ Lanjutkan dengan Event Ini
                </a>
            </div>

            <div class="flex items-center gap-3 mb-5">
                <div class="flex-1 h-px bg-gray-200"></div>
                <p class="text-xs font-semibold text-gray-400 flex-shrink-0">atau pilih event lain</p>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>
        @endif

        {{-- Daftar Event Aktif --}}
        @if ($events->count() > 0)
            <div class="anim-fade-up delay-3 space-y-3">
                @foreach ($events as $event)
                    <form action="{{ route('kasir.event.pilih') }}" method="POST">
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $event->id }}">
                        <button type="submit"
                            class="w-full text-left rounded-2xl border transition hover:shadow-md hover:-translate-y-0.5"
                            style="background: white;
                           border-color: {{ session('kasir_event_id') == $event->id ? '#7c3aed' : '#e2e8f0' }};
                           box-shadow: {{ session('kasir_event_id') == $event->id ? '0 0 0 2px #7c3aed' : '0 2px 8px rgba(0,0,0,0.04)' }}">

                            <div class="flex items-center justify-between p-5">
                                <div class="flex items-center gap-4 min-w-0 flex-1">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                                        style="background: {{ session('kasir_event_id') == $event->id ? 'linear-gradient(135deg, #5b21b6, #7c3aed)' : '#faf5ff' }}">
                                        <span class="text-xl">🎪</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-black text-gray-800 truncate">{{ $event->nama_event }}</p>
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            <span class="text-xs font-bold font-mono px-2 py-0.5 rounded-lg"
                                                style="background: #eff6ff; color: #1d4ed8">
                                                {{ $event->kode_event ?? 'NO-CODE' }}
                                            </span>
                                            <span class="text-xs text-gray-400">
                                                {{ $event->tanggal_mulai->format('d M') }} –
                                                {{ $event->tanggal_selesai->format('d M Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ml-3">
                                    @if (session('kasir_event_id') == $event->id)
                                        <span class="text-xs font-bold px-3 py-1.5 rounded-full"
                                            style="background: #faf5ff; color: #7c3aed; border: 1px solid #ddd6fe">
                                            ✓ Dipilih
                                        </span>
                                    @else
                                        <span class="text-xs font-bold px-3 py-1.5 rounded-full text-white"
                                            style="background: linear-gradient(135deg, #5b21b6, #7c3aed)">
                                            Pilih →
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Tarif --}}
                            @if ($event->tarifs && $event->tarifs->count() > 0)
                                <div class="px-5 pb-4 flex gap-2 flex-wrap"
                                    style="border-top: 1px solid #f5f3ff; padding-top: 12px">
                                    @php
                                        $urutanUkuran = ['S' => 1, 'M' => 2, 'L' => 3, 'XL' => 4, 'Gadget' => 5];
                                    @endphp
                                    @foreach ($event->tarifs->sortBy(fn($tarif) => $urutanUkuran[$tarif->ukuran] ?? 99) as $tarif)
                                        <span class="text-xs px-2.5 py-1 rounded-lg font-semibold"
                                            style="background: #f8faff; color: #64748b; border: 1px solid #e2e8f0">
                                            {{ $tarif->ukuran }}: Rp {{ number_format($tarif->harga, 0, ',', '.') }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                        </button>
                    </form>
                @endforeach
            </div>
        @else
            <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 p-12 text-center"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4"
                    style="background: #faf5ff">🎪</div>
                <p class="font-black text-gray-700 text-lg mb-1">Tidak Ada Event Aktif</p>
                <p class="text-gray-400 text-sm">Hubungi admin untuk mengaktifkan event.</p>
            </div>
        @endif

    </div>

</body>

</html>
