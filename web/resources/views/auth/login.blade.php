<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savve — Login</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-14px);
            }
        }

        @keyframes spin-slow {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes blob {

            0%,
            100% {
                border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            }

            50% {
                border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% center;
            }

            100% {
                background-position: 200% center;
            }
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(1);
                opacity: 0.5;
            }

            100% {
                transform: scale(1.8);
                opacity: 0;
            }
        }

        .animate-fade-up {
            animation: fadeUp 0.7s ease forwards;
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease forwards;
        }

        .animate-float {
            animation: float 4s ease-in-out infinite;
        }

        .animate-blob {
            animation: blob 8s ease-in-out infinite;
        }

        .animate-spin-slow {
            animation: spin-slow 25s linear infinite;
        }

        .delay-100 {
            animation-delay: 0.1s;
            opacity: 0;
        }

        .delay-200 {
            animation-delay: 0.2s;
            opacity: 0;
        }

        .delay-300 {
            animation-delay: 0.3s;
            opacity: 0;
        }

        .delay-400 {
            animation-delay: 0.4s;
            opacity: 0;
        }

        .delay-500 {
            animation-delay: 0.5s;
            opacity: 0;
        }

        .delay-600 {
            animation-delay: 0.6s;
            opacity: 0;
        }

        .delay-700 {
            animation-delay: 0.7s;
            opacity: 0;
        }

        .btn-shimmer {
            background: linear-gradient(135deg, #0a1628, #1a3a6b, #1e4d8c, #1a3a6b, #0a1628);
            background-size: 200% auto;
            animation: shimmer 3s linear infinite;
            box-shadow: 0 8px 32px rgba(15, 32, 68, 0.4);
            transition: all 0.3s ease;
        }

        .btn-shimmer:hover {
            box-shadow: 0 14px 40px rgba(15, 32, 68, 0.55);
            transform: translateY(-2px);
        }

        .btn-shimmer:active {
            transform: translateY(0) scale(0.99);
        }

        .input-field {
            transition: all 0.25s ease;
            border: 1.5px solid #e2e8f0;
            background: #f8faff;
        }

        .input-field:focus {
            border-color: #1a3a6b;
            box-shadow: 0 0 0 4px rgba(26, 58, 107, 0.1);
            outline: none;
            background: #fff;
        }

        .glass {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            animation: float 5s ease-in-out infinite;
        }

        .feature-card {
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateX(4px);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col lg:flex-row overflow-x-hidden" style="background: #f0f4f8;">

    {{-- ═══ LEFT PANEL ═══ --}}
    <div class="hidden lg:flex w-[48%] flex-col justify-between py-12 px-14 relative overflow-hidden flex-shrink-0"
        style="background: linear-gradient(150deg, #07111f 0%, #0c1e3d 35%, #0f2654 65%, #1a3a6b 100%); min-height: 100vh;">

        {{-- Decorative background --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            {{-- Blobs --}}
            <div class="animate-blob absolute opacity-20"
                style="width: 480px; height: 480px; top: -100px; right: -100px;
                       background: radial-gradient(circle, #1e4d8c 0%, #4a9eff 50%, transparent 80%)">
            </div>
            <div class="animate-blob absolute opacity-15"
                style="width: 380px; height: 380px; bottom: -80px; left: -80px;
                       background: radial-gradient(circle, #4a9eff 0%, #1a3a6b 60%, transparent 80%);
                       animation-delay: 3s">
            </div>
            <div class="animate-blob absolute opacity-10"
                style="width: 300px; height: 300px; top: 45%; left: 20%;
                       background: radial-gradient(circle, #93c5fd, transparent);
                       animation-delay: 1.5s">
            </div>

            {{-- Rotating rings --}}
            <div class="animate-spin-slow absolute rounded-full"
                style="width: 560px; height: 560px; top: 50%; left: 50%;
                       transform: translate(-50%, -50%);
                       border: 1px solid rgba(255,255,255,0.04)">
            </div>
            <div class="animate-spin-slow absolute rounded-full"
                style="width: 420px; height: 420px; top: 50%; left: 50%;
                       transform: translate(-50%, -50%);
                       border: 1px solid rgba(74,158,255,0.06);
                       animation-direction: reverse; animation-duration: 18s">
            </div>
            <div class="animate-spin-slow absolute rounded-full"
                style="width: 280px; height: 280px; top: 50%; left: 50%;
                       transform: translate(-50%, -50%);
                       border: 1px solid rgba(255,255,255,0.03);
                       animation-duration: 12s">
            </div>

            {{-- Floating particles --}}
            <div class="particle" style="width:8px;height:8px;top:18%;left:12%;animation-delay:0s"></div>
            <div class="particle" style="width:5px;height:5px;top:65%;left:82%;animation-delay:1.2s"></div>
            <div class="particle" style="width:4px;height:4px;top:82%;left:25%;animation-delay:2.4s"></div>
            <div class="particle" style="width:6px;height:6px;top:32%;left:75%;animation-delay:0.6s"></div>
            <div class="particle" style="width:3px;height:3px;top:12%;left:58%;animation-delay:1.8s"></div>
            <div class="particle" style="width:7px;height:7px;top:55%;left:8%;animation-delay:3s"></div>
        </div>

        {{-- Logo --}}
        <div class="relative z-10 animate-fade-up flex justify-start">
            <img src="{{ asset('images/logo.png') }}" alt="Savve Logo"
                class="h-16 w-auto object-contain drop-shadow-lg">
        </div>

        {{-- Main Content --}}
        <div class="relative z-10 flex-1 flex flex-col justify-center py-8">

            {{-- Live badge --}}
            <div
                class="animate-fade-up delay-100 inline-flex items-center gap-2.5 glass px-4 py-2.5 rounded-full mb-8 self-start">
                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse flex-shrink-0"></span>
                <span class="text-xs font-semibold" style="color: #93c5fd">Sistem aktif & berjalan optimal</span>
            </div>

            {{-- Headline --}}
            <h1 class="animate-fade-up delay-200 font-black text-white leading-[1.05] mb-6"
                style="font-size: clamp(2.2rem, 4vw, 3.2rem)">
                Kelola<br>
                <span
                    style="background: linear-gradient(90deg, #4a9eff 0%, #93c5fd 100%);
                             -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Penitipan
                </span><br>
                Lebih Cerdas
            </h1>

            <p class="animate-fade-up delay-300 text-sm leading-relaxed mb-8 max-w-xs" style="color: #93c5fd">
                Platform manajemen penitipan barang berbasis web untuk operasional event yang efisien, akurat, dan
                terstruktur.
            </p>

            {{-- Feature Cards --}}
            <div class="animate-fade-up delay-400 space-y-3">
                @foreach ([['icon' => '🔄', 'title' => 'Transaksi real-time & otomatis', 'sub' => 'Eliminasi pencatatan manual'], ['icon' => '🔒', 'title' => 'Nomor transaksi unik & tervalidasi', 'sub' => 'Anti duplikasi & konflik'], ['icon' => '📊', 'title' => 'Laporan & export Excel instan', 'sub' => 'Data akurat setiap saat']] as $f)
                    <div class="feature-card glass rounded-2xl px-4 py-3.5 flex items-center gap-4 cursor-default">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
                            style="background: rgba(74,158,255,0.18)">{{ $f['icon'] }}</div>
                        <div>
                            <p class="text-white text-sm font-semibold leading-tight">{{ $f['title'] }}</p>
                            <p class="text-xs mt-0.5" style="color: #60a5fa">{{ $f['sub'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Footer --}}
        <div class="relative z-10 animate-fade-up delay-500">
            <p class="text-xs" style="color: #3b5a8a">© {{ date('Y') }} Vendor Savve. All rights reserved.</p>
        </div>
    </div>

    {{-- ═══ RIGHT PANEL ═══ --}}
    <div class="flex-1 flex items-center justify-center px-6 py-10 lg:px-12 lg:py-0 relative overflow-hidden"
        style="min-height: 100vh;">

        {{-- Subtle background shapes --}}
        <div class="absolute top-0 right-0 pointer-events-none"
            style="width: 350px; height: 350px;
                   background: radial-gradient(circle, rgba(219,234,254,0.6), transparent);
                   transform: translate(30%, -30%)">
        </div>
        <div class="absolute bottom-0 left-0 pointer-events-none"
            style="width: 280px; height: 280px;
                   background: radial-gradient(circle, rgba(219,234,254,0.4), transparent);
                   transform: translate(-30%, 30%)">
        </div>

        <div class="w-full max-w-[400px] relative z-10">

            {{-- Mobile Logo --}}
            <div class="lg:hidden flex justify-center mb-8 animate-fade-up">
                <img src="{{ asset('images/logo.png') }}" alt="Savve Logo" class="h-14 w-auto object-contain">
            </div>

            {{-- Greeting --}}
            <div class="animate-fade-up delay-100 mb-7">
                <p class="text-xs font-bold uppercase tracking-widest mb-2" style="color: #1a3a6b">
                    Selamat Datang Kembali 👋
                </p>
                <h2 class="font-black text-gray-900 leading-tight" style="font-size: 1.85rem">
                    Masuk ke<br>Akun Anda
                </h2>
                <p class="text-gray-400 text-sm mt-2">Kelola operasional penitipan barang hari ini.</p>
            </div>

            {{-- Error Alert --}}
            @if ($errors->any())
                <div class="animate-fade-up mb-5 flex items-start gap-3 px-4 py-3.5 rounded-2xl"
                    style="background: #fff5f5; border: 1.5px solid #fecaca;">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                        style="background: #fee2e2">
                        <span class="text-red-500 text-xs font-bold">!</span>
                    </div>
                    <div>
                        <p class="text-red-700 font-semibold text-sm">Email atau password salah</p>
                        <p class="text-red-400 text-xs mt-0.5">Periksa kembali dan coba lagi.</p>
                    </div>
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div class="animate-fade-up delay-200">
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: #475569">
                        Email
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">✉</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="input-field w-full rounded-xl pl-11 pr-4 py-3.5 text-sm text-gray-800 placeholder-gray-400"
                            placeholder="email@example.com">
                    </div>
                </div>

                {{-- Password --}}
                <div class="animate-fade-up delay-300">
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: #475569">
                        Password
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔒</span>
                        <input type="password" name="password" id="password" required
                            class="input-field w-full rounded-xl pl-11 pr-12 py-3.5 text-sm text-gray-800 placeholder-gray-400"
                            placeholder="Masukkan password">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <span id="eye-icon" class="text-sm">👁️</span>
                        </button>
                    </div>
                </div>

                {{-- Remember --}}
                <div class="animate-fade-up delay-400 flex items-center gap-2.5">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded cursor-pointer"
                        style="accent-color: #1a3a6b">
                    <label for="remember" class="text-sm text-gray-500 cursor-pointer select-none">
                        Ingat saya
                    </label>
                </div>

                {{-- Submit --}}
                <div class="animate-fade-up delay-500 pt-1">
                    <button type="submit"
                        class="btn-shimmer w-full py-4 rounded-xl text-white font-bold text-sm tracking-wide flex items-center justify-center gap-2">
                        Masuk ke Dashboard
                        <span class="text-base">→</span>
                    </button>
                </div>
            </form>

            {{-- Divider --}}
            <div class="animate-fade-up delay-600 flex items-center gap-3 my-5">
                <div class="flex-1 h-px" style="background: #e2e8f0"></div>

                <p class="text-xs font-semibold whitespace-nowrap" style="color: #94a3b8">
                    Hak Akses Sistem
                </p>

                <div class="flex-1 h-px" style="background: #e2e8f0"></div>
            </div>



            {{-- Role Cards --}}
            <div class="animate-fade-up delay-700 grid grid-cols-2 gap-3">
                <div class="p-4 rounded-2xl bg-white cursor-default transition hover:shadow-md"
                    style="border: 1.5px solid #e2e8f0">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg mb-3"
                        style="background: #eff6ff">⚙️</div>
                    <p class="text-sm font-bold text-gray-800">Admin</p>
                    <p class="text-xs text-gray-400 mt-0.5">Full system access</p>
                    <div class="mt-3 flex gap-1">
                        <div class="h-1 w-7 rounded-full" style="background: #1a3a6b"></div>
                        <div class="h-1 w-4 rounded-full" style="background: #dbeafe"></div>
                        <div class="h-1 w-2 rounded-full" style="background: #dbeafe"></div>
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-white cursor-default transition hover:shadow-md"
                    style="border: 1.5px solid #e2e8f0">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg mb-3"
                        style="background: #f5f3ff">🧾</div>
                    <p class="text-sm font-bold text-gray-800">Kasir</p>
                    <p class="text-xs text-gray-400 mt-0.5">Storage flow access</p>
                    <div class="mt-3 flex gap-1">
                        <div class="h-1 w-5 rounded-full" style="background: #7c3aed"></div>
                        <div class="h-1 w-4 rounded-full" style="background: #ede9fe"></div>
                        <div class="h-1 w-2 rounded-full" style="background: #ede9fe"></div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <p class="animate-fade-up delay-700 text-center text-xs mt-8" style="color: #cbd5e1">
                © {{ date('Y') }} Vendor Savve Information Systems
            </p>

        </div>
    </div>

</body>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = '🙈';
        } else {
            input.type = 'password';
            icon.textContent = '👁️';
        }
    }
</script>

</html>
