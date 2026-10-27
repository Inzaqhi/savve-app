<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savve Kasir — @yield('title')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ── DataTables Custom Kasir ── */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1.5px solid #ede9fe !important;
            border-radius: 10px !important;
            padding: 6px 12px !important;
            font-size: 13px !important;
            background: #faf5ff !important;
            color: #374151 !important;
            outline: none !important;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #a78bfa !important;
            box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1) !important;
        }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            font-size: 12px !important;
            color: #64748b !important;
            font-weight: 600 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .dataTables_wrapper .dataTables_info {
            font-size: 12px !important;
            color: #94a3b8 !important;
            padding-top: 8px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            border: 1.5px solid #ede9fe !important;
            background: #faf5ff !important;
            color: #374151 !important;
            padding: 4px 10px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            margin: 0 2px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f3e8ff !important;
            border-color: #a78bfa !important;
            color: #5b21b6 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #5b21b6, #7c3aed) !important;
            border-color: #7c3aed !important;
            color: white !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.4 !important;
            cursor: not-allowed !important;
        }

        /* ── DataTables Sorting Icons ── */
        table.dataTable thead th.sorting,
        table.dataTable thead th.sorting_asc,
        table.dataTable thead th.sorting_desc {
            padding-right: 26px !important;
            position: relative !important;
            cursor: pointer !important;
        }

        table.dataTable thead th.sorting::after,
        table.dataTable thead th.sorting_asc::after,
        table.dataTable thead th.sorting_desc::after {
            position: absolute !important;
            right: 8px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            font-size: 11px !important;
            line-height: 1 !important;
        }

        /* Default — belum di-sort */
        table.dataTable thead th.sorting::after {
            content: '▲▼' !important;
            color: #cbd5e1 !important;
            font-size: 9px !important;
        }

        table.dataTable thead th.sorting_asc::after {
            content: '▲' !important;
            color: #7c3aed !important;
        }

        table.dataTable thead th.sorting_desc::after {
            content: '▼' !important;
            color: #7c3aed !important;
        }

        table.dataTable thead th.sorting:hover::after {
            color: #a78bfa !important;
        }

        .dt-buttons .dt-button {
            border-radius: 10px !important;
            border: 1.5px solid #ede9fe !important;
            background: #faf5ff !important;
            color: #374151 !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            padding: 6px 14px !important;
            margin-right: 4px !important;
            box-shadow: none !important;
        }

        .dt-buttons .dt-button:hover {
            background: linear-gradient(135deg, #5b21b6, #7c3aed) !important;
            color: white !important;
            border-color: #7c3aed !important;
        }

        table.dataTable thead th {
            border-bottom: 2px solid #ede9fe !important;
        }

        table.dataTable tbody tr:hover {
            background: #fdfbff !important;
        }

        table.dataTable.no-footer {
            border-bottom: none !important;
        }

        .dataTables_wrapper {
            padding: 0 !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            padding: 16px 20px 12px !important;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            padding: 12px 20px 16px !important;
            border-top: 1px solid #f5f3ff !important;
        }

        * {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        #sidebar-kasir {
            transition: transform 0.3s ease;
        }

        #sidebar-overlay-kasir {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 40;
            backdrop-filter: blur(2px);
        }

        #sidebar-overlay-kasir.active {
            display: block;
        }

        @media (max-width: 1023px) {
            #sidebar-kasir {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                /* fallback untuk browser lama yang belum support dvh */
                height: 100dvh;
                /* tinggi viewport asli, ikut menyesuaikan toolbar browser HP */
                z-index: 50;
                transform: translateX(-100%);
                overflow-y: auto;
                /* jaring pengaman: kalau konten tetap lebih tinggi dari layar, bisa di-scroll */
            }

            #sidebar-kasir.open {
                transform: translateX(0);
            }
        }

        .kasir-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            color: #94a3b8;
            transition: all 0.2s ease;
            position: relative;
            text-decoration: none;
        }

        .kasir-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #e2e8f0;
        }

        .kasir-link.active {
            background: rgba(167, 139, 250, 0.15);
            color: #fff;
            font-weight: 600;
        }

        .kasir-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            height: 60%;
            width: 3px;
            background: #a78bfa;
            border-radius: 0 4px 4px 0;
        }

        .stat-card {
            transition: all 0.3s ease;
            cursor: default;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
        }

        .table-row {
            transition: background 0.15s ease;
        }

        .table-row:hover {
            background: #fdfbff;
        }

        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
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

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .anim-fade-up {
            animation: fadeUp 0.5s ease forwards;
        }

        .anim-slide-down {
            animation: slideDown 0.4s ease forwards;
        }

        .anim-fade-in {
            animation: fadeUp 0.5s ease forwards;
        }

        .anim-scale-in {
            animation: fadeUp 0.4s ease forwards;
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

        .delay-5 {
            animation-delay: 0.25s;
            opacity: 0;
        }

        .delay-6 {
            animation-delay: 0.30s;
            opacity: 0;
        }

        .delay-7 {
            animation-delay: 0.35s;
            opacity: 0;
        }

        .delay-8 {
            animation-delay: 0.40s;
            opacity: 0;
        }

        video {
            transform: scaleX(1) !important;
            -webkit-transform: scaleX(1) !important;
            object-fit: cover;
            display: block;
            max-width: 100%;
        }

        {{-- DataTables --}}
        <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/jquery.dataTables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/buttons.dataTables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/responsive.dataTables.min.css') }}">
    </style>
</head>

<body style="background: #f5f3ff;">

    {{-- Overlay --}}
    <div id="sidebar-overlay-kasir" onclick="closeSidebarKasir()"></div>

    <div class="flex h-screen overflow-hidden">

        {{-- ═══ SIDEBAR ═══ --}}
        <aside id="sidebar-kasir" class="w-[220px] flex-shrink-0 flex flex-col lg:relative lg:translate-x-0"
            style="background: linear-gradient(180deg, #1e1035 0%, #2d1b69 40%, #3b2180 100%);">

            {{-- Logo --}}
            <div class="px-5 py-4 flex items-center justify-between"
                style="border-bottom: 1px solid rgba(255,255,255,0.06)">
                <div class="flex-1 flex items-center justify-center">
                    <img src="{{ asset('images/Logo.png') }}" alt="Savve Logo" class="h-12 w-auto object-contain">
                </div>
                <button onclick="closeSidebarKasir()"
                    class="lg:hidden w-7 h-7 flex items-center justify-center rounded-lg text-white flex-shrink-0 ml-2"
                    style="background: rgba(255,255,255,0.1)">
                    ✕
                </button>
            </div>

            {{-- Role Badge --}}
            <div class="mx-3 mt-3 px-3 py-2 rounded-xl flex items-center gap-2"
                style="background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.2)">
                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: #34d399"></span>
                <p class="text-xs font-bold" style="color: #34d399">CASHIER MODE AKTIF</p>
            </div>

            {{-- Event Aktif Badge --}}
            @if (session('kasir_event_id'))
                <div class="mx-3 mt-2 px-3 py-2.5 rounded-xl"
                    style="background: rgba(167,139,250,0.08); border: 1px solid rgba(167,139,250,0.15)">
                    <p class="text-xs font-semibold uppercase tracking-wider mb-0.5"
                        style="color: rgba(255,255,255,0.4); font-size: 9px">Event Aktif</p>
                    <p class="text-white text-xs font-bold truncate">{{ session('kasir_event_nama') }}</p>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs font-mono font-bold"
                            style="color: #a78bfa">{{ session('kasir_event_kode') }}</span>
                        <form action="{{ route('kasir.event.ganti') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs" style="color: rgba(255,255,255,0.3)"
                                onclick="return confirm('Ganti event?')">Ganti</button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Nav --}}
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                <p class="text-xs font-bold px-3 mb-2 mt-1"
                    style="color: rgb(255, 255, 255); letter-spacing: 0.1em; text-transform: uppercase; font-size: 9px">
                    Menu Utama
                </p>
                <a href="{{ route('kasir.dashboard') }}"
                    class="kasir-link {{ request()->routeIs('kasir.dashboard') ? 'active' : '' }}">
                    <span class="w-5 text-center text-sm">⊞</span> Dashboard
                </a>
                <a href="{{ route('kasir.transaksi.create') }}"
                    class="kasir-link {{ request()->routeIs('kasir.transaksi.create') ? 'active' : '' }}">
                    <span class="w-5 text-center text-sm">➕</span> Titip Barang
                </a>
                <a href="{{ route('kasir.pengambilan.index') }}"
                    class="kasir-link {{ request()->routeIs('kasir.pengambilan.*') ? 'active' : '' }}">
                    <span class="w-5 text-center text-sm">📦</span> Ambil Barang
                </a>
                <a href="{{ route('kasir.transaksi.index') }}"
                    class="kasir-link {{ request()->routeIs('kasir.transaksi.index') ? 'active' : '' }}">
                    <span class="w-5 text-center text-sm">📋</span> Riwayat Transaksi
                </a>
            </nav>

            {{-- User --}}
            <div class="px-3 py-4" style="border-top: 1px solid rgba(255,255,255,0.06)">
                <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl mb-2"
                    style="background: rgba(255,255,255,0.04)">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-sm text-white flex-shrink-0"
                        style="background: linear-gradient(135deg, #5b21b6, #a78bfa)">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-white text-xs font-semibold truncate leading-none">{{ auth()->user()->name }}
                        </p>
                        <p class="text-xs mt-0.5" style="color: #a78bfa">Kasir</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="kasir-link w-full text-left" style="color: #f87171 !important">
                        <span class="w-5 text-center text-sm">🚪</span> Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- ═══ MAIN ═══ --}}
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">

            {{-- Topbar --}}
            <header class="bg-white flex-shrink-0 flex items-center justify-between px-4 lg:px-6 py-3.5"
                style="border-bottom: 1px solid #ede9fe; box-shadow: 0 1px 8px rgba(0,0,0,0.04)">

                <div class="flex items-center gap-3 flex-1">
                    {{-- Hamburger --}}
                    <button onclick="openSidebarKasir()"
                        class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl flex-shrink-0"
                        style="background: #faf5ff; border: 1.5px solid #ede9fe">
                        <span class="text-gray-600">☰</span>
                    </button>

                    {{-- Search --}}
                    <div class="flex-1 max-w-xs hidden sm:block">
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"
                                style="font-size: 12px">🔍</span>
                            <form method="GET" action="{{ route('kasir.search') }}" class="w-full">
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"
                                        style="font-size: 12px">🔍</span>
                                    <input type="text" name="q" value="{{ request('q') }}"
                                        placeholder="Cari transaksi atau nama barang..."
                                        class="w-full rounded-xl pl-9 pr-4 py-2.5 text-sm transition"
                                        style="background: #faf5ff; border: 1.5px solid #ede9fe; font-size: 13px; color: #374151"
                                        onfocus="this.style.borderColor='#a78bfa'; this.style.boxShadow='0 0 0 3px rgba(167,139,250,0.1)'"
                                        onblur="this.style.borderColor='#ede9fe'; this.style.boxShadow='none'">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('kasir.profile') }}"
                        class="flex items-center gap-2.5 pl-2 lg:pl-3 hover:opacity-80 transition"
                        style="border-left: 1.5px solid #ede9fe">
                        <div class="text-right hidden md:block">
                            <p class="text-xs font-bold leading-none" style="color: #1e1035">
                                {{ auth()->user()->name }}</p>
                            <p class="text-xs mt-0.5" style="color: #a78bfa">Kasir Mode</p>
                        </div>
                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-sm text-white flex-shrink-0"
                            style="background: linear-gradient(135deg, #5b21b6, #a78bfa)">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </a>
                </div>
            </header>

            {{-- Content --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                @if (session('success'))
                    <div class="anim-slide-down mb-5 flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold"
                        style="background: #faf5ff; border: 1.5px solid #ddd6fe; color: #7c3aed">
                        <span>✓</span> {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="anim-slide-down mb-5 flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold"
                        style="background: #fff5f5; border: 1.5px solid #fecaca; color: #dc2626">
                        <span>⚠</span> {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function openSidebarKasir() {
            document.getElementById('sidebar-kasir').classList.add('open');
            document.getElementById('sidebar-overlay-kasir').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebarKasir() {
            document.getElementById('sidebar-kasir').classList.remove('open');
            document.getElementById('sidebar-overlay-kasir').classList.remove('active');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.kasir-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) {
                    closeSidebarKasir();
                }
            });
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeSidebarKasir();
            }
        });
    </script>

    {{-- DataTables JS --}}
    <script src="{{ asset('assets/vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/pdfmake/vfs_fonts.js') }}"></script>

    @stack('scripts')

</body>

</html>
