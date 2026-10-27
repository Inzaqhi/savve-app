@extends('layouts.admin')
@section('title', 'Tambah Kasir')

@section('content')

    <div class="anim-fade-up delay-1 flex flex-row justify-between items-start gap-3 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #1a3a6b">Management</p>
            <h1 class="text-xl lg:text-2xl font-black text-gray-900">Tambah Kasir</h1>
            <p class="text-gray-400 text-sm mt-1">Buat akun baru untuk kasir.</p>
        </div>
        <a href="{{ route('admin.events.index') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition self-start flex-shrink-0"
            style="background: white; border: 1.5px solid #e2e8f0; color: #374151">
            ← Kembali
        </a>
    </div>

    <div class="flex flex-col-reverse lg:flex-row gap-6">

        {{-- ═══ KIRI — Form ═══ --}}
        <div class="flex-1">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <div class="anim-fade-up delay-2 bg-white rounded-2xl border border-gray-100 p-5 lg:p-6 mb-4"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <h3 class="font-black text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-white text-xs flex-shrink-0"
                            style="background: linear-gradient(135deg, #0f2044, #1e4d8c)">1</span>
                        Informasi Akun
                    </h3>

                    <div class="mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color: #64748b">Nama
                            Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="w-full rounded-xl px-4 py-3 text-sm transition"
                            style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                            placeholder="Nama lengkap kasir"
                            onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                            style="color: #64748b">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full rounded-xl px-4 py-3 text-sm transition"
                            style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                            placeholder="email@example.com"
                            onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 p-5 lg:p-6 mb-4"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <h3 class="font-black text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-white text-xs flex-shrink-0"
                            style="background: linear-gradient(135deg, #0f2044, #1e4d8c)">2</span>
                        Keamanan Akun
                    </h3>

                    <div class="mb-4">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                            style="color: #64748b">Password</label>
                        <input type="password" name="password" class="w-full rounded-xl px-4 py-3 text-sm transition"
                            style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                            placeholder="Minimal 6 karakter"
                            onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                            style="color: #64748b">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full rounded-xl px-4 py-3 text-sm transition"
                            style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                            placeholder="Ulangi password"
                            onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    </div>
                </div>

                <div class="anim-fade-up delay-4 flex gap-3">
                    <button type="submit"
                        class="flex-1 flex items-center justify-center gap-2 py-3.5 rounded-xl text-white font-bold text-sm transition hover:opacity-90"
                        style="background: linear-gradient(135deg, #0f2044, #1e4d8c); box-shadow: 0 4px 12px rgba(15,32,68,0.2)">
                        💾 Simpan Kasir
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="px-4 lg:px-6 py-3.5 rounded-xl font-bold text-sm flex-shrink-0"
                        style="background: #f1f5f9; color: #64748b">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- ═══ KANAN — Info (atas di mobile, kanan di desktop) ═══ --}}
        <div class="w-full lg:w-64 flex-shrink-0">
            <div class="anim-fade-up delay-2 rounded-2xl p-5 text-white lg:sticky lg:top-6"
                style="background: linear-gradient(150deg, #091629, #0f2044, #1a3a6b)">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl mb-3"
                    style="background: rgba(74,158,255,0.2)">👤</div>
                <p class="font-black text-base mb-3">Info Akun Kasir</p>
                <ul class="space-y-2.5">
                    @foreach (['Kasir hanya bisa akses menu transaksi & pengambilan.', 'Password minimal 6 karakter.', 'Kasir login menggunakan email & password.', 'Akun bisa diedit atau dihapus kapan saja.'] as $tip)
                        <li class="flex items-start gap-2 text-xs leading-relaxed" style="color: #93c5fd">
                            <span class="flex-shrink-0 mt-0.5">•</span> {{ $tip }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

@endsection
