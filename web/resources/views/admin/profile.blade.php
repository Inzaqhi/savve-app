@extends('layouts.admin')
@section('title', 'Profil Saya')

@section('content')

    <div class="anim-fade-up delay-1 mb-6">
        <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #1a3a6b">Akun</p>
        <h1 class="text-xl lg:text-2xl font-black text-gray-900">Profil Saya</h1>
        <p class="text-gray-400 text-sm mt-1">Kelola informasi akun dan keamanan password kamu.</p>
    </div>

    <div class="flex flex-col-reverse lg:flex-row gap-6">

        {{-- ═══ KIRI — Form ═══ --}}
        <div class="flex-1 space-y-4">

            <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 overflow-hidden"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">

                {{-- Tab Header --}}
                <div class="flex" style="border-bottom: 2px solid #f1f5f9">
                    <button onclick="switchTab('info')" id="tab-info"
                        class="flex-1 flex items-center justify-center gap-2 py-3.5 text-sm font-bold transition profile-tab active-profile-tab">
                        👤 Informasi Profil
                    </button>
                    <button onclick="switchTab('password')" id="tab-password"
                        class="flex-1 flex items-center justify-center gap-2 py-3.5 text-sm font-bold transition profile-tab">
                        🔒 Ganti Password
                    </button>
                </div>

                {{-- Tab Info --}}
                <div id="panel-info" class="p-5 lg:p-6">
                    @if (session('success') && !session('tab'))
                        <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold"
                            style="background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #15803d">
                            ✓ {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-4">
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                style="color: #64748b">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full rounded-xl px-4 py-3 text-sm transition"
                                style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                                onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                style="color: #64748b">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="w-full rounded-xl px-4 py-3 text-sm transition"
                                style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                                onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="flex items-center gap-2 px-6 py-3 rounded-xl text-white font-bold text-sm transition hover:opacity-90"
                            style="background: linear-gradient(135deg, #0f2044, #1e4d8c); box-shadow: 0 4px 12px rgba(15,32,68,0.2)">
                            💾 Simpan Perubahan
                        </button>
                    </form>
                </div>

                {{-- Tab Password --}}
                <div id="panel-password" class="p-5 lg:p-6 hidden">
                    @if (session('success') && session('tab') === 'password')
                        <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold"
                            style="background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #15803d">
                            ✓ {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.profile.password') }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-4">
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                style="color: #64748b">Password Saat Ini</label>
                            <input type="password" name="current_password"
                                class="w-full rounded-xl px-4 py-3 text-sm transition"
                                style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                                placeholder="Masukkan password saat ini"
                                onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            @error('current_password')
                                <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                style="color: #64748b">Password Baru</label>
                            <input type="password" name="password" class="w-full rounded-xl px-4 py-3 text-sm transition"
                                style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                                placeholder="Minimal 6 karakter"
                                onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                style="color: #64748b">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation"
                                class="w-full rounded-xl px-4 py-3 text-sm transition"
                                style="background: #f8faff; border: 1.5px solid #e2e8f0; color: #1e293b"
                                placeholder="Ulangi password baru"
                                onfocus="this.style.borderColor='#4a9eff'; this.style.boxShadow='0 0 0 3px rgba(74,158,255,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        </div>

                        <button type="submit"
                            class="flex items-center gap-2 px-6 py-3 rounded-xl text-white font-bold text-sm transition hover:opacity-90"
                            style="background: linear-gradient(135deg, #0f2044, #1e4d8c); box-shadow: 0 4px 12px rgba(15,32,68,0.2)">
                            🔒 Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ═══ KANAN — Info (atas di mobile, kanan di desktop) ═══ --}}
        <div class="w-full lg:w-72 flex-shrink-0 space-y-4">

            {{-- Avatar & Info --}}
            <div class="anim-fade-up delay-2 bg-white rounded-2xl border border-gray-100 p-5"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                <div class="flex items-center gap-4 mb-4 pb-4" style="border-bottom: 1px solid #f1f5f9">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center font-black text-2xl text-white flex-shrink-0"
                        style="background: linear-gradient(135deg, #0f2044, #4a9eff)">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-black text-gray-800 text-base leading-tight truncate">{{ $user->name }}</p>
                        <p class="text-gray-400 text-xs mt-0.5 truncate">{{ $user->email }}</p>
                        <div class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold"
                            style="background: #eff6ff; color: #1d4ed8">
                            ⚙️ Administrator
                        </div>
                    </div>
                </div>

                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color: #94a3b8">Statistik Akun</p>
                <div class="space-y-2.5">
                    @foreach ([['Bergabung', $user->created_at->format('d M Y'), '#374151'], ['Role', 'Administrator', '#374151'], ['Total Event', \App\Models\Event::count() . ' event', '#0f2044'], ['Total Kasir', \App\Models\User::where('role', 'kasir')->count() . ' kasir', '#0f2044']] as $s)
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-400">{{ $s[0] }}</span>
                            <span class="text-xs font-bold"
                                style="color: {{ $s[2] }}">{{ $s[1] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tips Keamanan --}}
            <div class="anim-fade-up delay-3 rounded-2xl p-5 text-white"
                style="background: linear-gradient(135deg, #1e293b, #334155)">
                <p class="font-bold text-sm mb-2.5">🔐 Tips Keamanan</p>
                <ul class="space-y-1.5">
                    @foreach (['Ganti password secara berkala', 'Jangan bagikan password ke siapapun', 'Gunakan kombinasi huruf & angka', 'Minimal 6 karakter'] as $tip)
                        <li class="text-xs flex items-start gap-1.5" style="color: #94a3b8">
                            <span class="flex-shrink-0 mt-0.5">•</span> {{ $tip }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>

    <style>
        .profile-tab {
            color: #94a3b8;
            border-bottom: 2px solid transparent;
        }

        .active-profile-tab {
            color: #0f2044;
            border-bottom: 2px solid #1a3a6b;
            background: #f8faff;
        }
    </style>

    <script>
        function switchTab(tab) {
            ['info', 'password'].forEach(t => {
                document.getElementById('panel-' + t).classList.add('hidden');
                document.getElementById('tab-' + t).classList.remove('active-profile-tab');
            });
            document.getElementById('panel-' + tab).classList.remove('hidden');
            document.getElementById('tab-' + tab).classList.add('active-profile-tab');
        }

        @if (session('tab') === 'password' || $errors->has('current_password') || $errors->has('password'))
            switchTab('password');
        @endif
    </script>

@endsection
