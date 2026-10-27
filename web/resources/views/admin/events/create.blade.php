@extends('layouts.admin')
@section('title', 'Tambah Event')

@section('content')

    <div class="anim-fade-up delay-1 flex justify-between items-start mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #1a3a6b">Management</p>
            <h1 class="text-xl lg:text-2xl font-black text-gray-900">Tambah Event</h1>
            <p class="text-gray-400 text-sm mt-1">Buat event baru dan setting tarif penitipan.</p>
        </div>
        <a href="{{ route('admin.events.index') }}"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition self-start flex-shrink-0"
            style="background: white; border: 1.5px solid #e2e8f0; color: #374151">
            ← Kembali
        </a>
    </div>

    <div class="flex flex-col-reverse lg:flex-row gap-6">

        {{-- ═══ KIRI — Form ═══ --}}
        <div class="flex-1">
            <form action="{{ route('admin.events.store') }}" method="POST">
                @csrf

                {{-- Informasi Event --}}
                <div class="anim-fade-up delay-2 bg-white rounded-2xl border border-gray-100 p-5 lg:p-6 mb-4"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <h3 class="font-black text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-white text-xs flex-shrink-0"
                            style="background: linear-gradient(135deg, #0f2044, #1e4d8c)">1</span>
                        Informasi Event
                    </h3>

                    <div class="mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color: #64748b">
                            Nama Event
                        </label>
                        <input type="text" name="nama_event" value="{{ old('nama_event') }}"
                            class="w-full rounded-xl px-4 py-3 text-sm transition"
                            style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                            placeholder="Contoh: Konser Dewa 19"
                            onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">

                        @error('nama_event')
                            <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color: #64748b">Kode
                            Event</label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="kode_event" value="{{ old('kode_event') }}"
                                class="w-full rounded-xl px-4 py-3 text-sm transition"
                                style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b; text-transform: uppercase"
                                placeholder="Contoh: PLP25, SOD26" maxlength="20"
                                oninput="this.value = this.value.toUpperCase()"
                                onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        </div>
                        <p class="text-xs mt-1" style="color: #94a3b8">
                            Digunakan sebagai kode pada nomor transaksi. Contoh: SVV-<strong>SOD25</strong>-0001
                        </p>
                        @error('kode_event')
                            <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                style="color: #64748b">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                                class="w-full rounded-xl px-4 py-3 text-sm transition"
                                style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                                onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            @error('tanggal_mulai')
                                <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                style="color: #64748b">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                                class="w-full rounded-xl px-4 py-3 text-sm transition"
                                style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                                onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            @error('tanggal_selesai')
                                <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Tarif --}}
                <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 p-5 lg:p-6 mb-4"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <h3 class="font-black text-gray-800 mb-1 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-white text-xs flex-shrink-0"
                            style="background: linear-gradient(135deg, #0f2044, #1e4d8c)">2</span>
                        Tarif per Ukuran
                    </h3>
                    <p class="text-xs text-gray-400 mb-4 ml-8">Setting harga penitipan berdasarkan ukuran barang.</p>

                    <style>
                        @media (max-width: 639px) {
                            .tarif-gadget-card {
                                grid-column: 1 / -1;
                            }
                        }
                    </style>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        @foreach (['S' => 'Small', 'M' => 'Medium', 'L' => 'Large', 'XL' => 'Extra Large', 'Gadget' => 'Gadget'] as $kode => $label)
                            <div class="rounded-xl p-3 flex flex-col gap-2.5 {{ $kode === 'Gadget' ? 'tarif-gadget-card' : '' }}"
                                style="background:#f8faff;border:1.5px solid #e2e8f0">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="px-2 py-1 rounded-lg text-xs font-black text-white flex-shrink-0"
                                        style="background: linear-gradient(135deg, #0f2044, #1e4d8c)">{{ $kode }}</span>
                                    <span class="text-xs font-medium truncate"
                                        style="color: #64748b">{{ $label }}</span>
                                </div>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold pointer-events-none"
                                        style="color: #94a3b8">Rp</span>
                                    <input type="number" name="tarif[{{ $kode }}]"
                                        value="{{ old('tarif.' . $kode) }}"
                                        class="w-full rounded-xl pl-9 pr-2 py-2.5 text-sm font-semibold transition"
                                        style="background: white; border: 1.5px solid #e2e8f0; color: #1e293b"
                                        placeholder="0" min="0"
                                        onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                                </div>
                                @error('tarif.' . $kode)
                                    <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="anim-fade-up delay-4 flex gap-3">
                    <button type="submit"
                        class="flex-1 flex items-center justify-center gap-2 py-3.5 rounded-xl text-white font-bold text-sm transition hover:opacity-90"
                        style="background: linear-gradient(135deg, #0f2044, #1e4d8c); box-shadow: 0 4px 12px rgba(15,32,68,0.2)">
                        💾 Simpan Event
                    </button>
                    <a href="{{ route('admin.events.index') }}"
                        class="px-4 lg:px-6 py-3.5 rounded-xl font-bold text-sm transition flex-shrink-0"
                        style="background: #f1f5f9; color: #64748b">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- ═══ KANAN — Tips (atas di mobile, kanan di desktop) ═══ --}}
        <div class="w-full lg:w-64 flex-shrink-0">
            <div class="anim-fade-up delay-2 rounded-2xl p-5 text-white lg:sticky lg:top-6"
                style="background: linear-gradient(150deg, #091629, #0f2044, #1a3a6b)">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl mb-3"
                    style="background: rgba(74,158,255,0.2)">🎪</div>
                <p class="font-black text-base mb-3">Tips Setting Event</p>
                <ul class="space-y-2.5">
                    @foreach (['Pastikan tanggal mulai dan selesai sudah benar.', 'Tarif otomatis dipakai saat kasir input transaksi.', 'Event nonaktif tidak bisa digunakan kasir.', 'Tarif bisa diubah kapan saja via menu Edit.'] as $tip)
                        <li class="flex items-start gap-2 text-xs leading-relaxed" style="color: #93c5fd">
                            <span class="text-blue-400 mt-0.5 flex-shrink-0">•</span>{{ $tip }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

@endsection
