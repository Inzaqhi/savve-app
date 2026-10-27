@extends('layouts.admin')
@section('title', 'Laporan Harian')

@section('content')

    {{-- Header --}}
    <div class="anim-fade-up delay-1 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #1a3a6b">Laporan</p>
            <h1 class="text-xl lg:text-2xl font-black text-gray-900">Laporan Harian</h1>
            <p class="text-gray-400 text-sm mt-1">Pantau aktivitas penitipan barang masuk dan keluar secara real-time.</p>
        </div>
        <a href="{{ route('admin.laporan.export', request()->query()) }}"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-bold text-sm transition hover:opacity-90 self-start flex-shrink-0"
            style="background: linear-gradient(135deg, #0f2044, #1e4d8c); box-shadow: 0 4px 12px rgba(15,32,68,0.2)">
            ⬇ Export Excel
        </a>
    </div>

    {{-- ─── Filter Laporan Harian Admin ──────────────────────────────────── --}}
    <div class="lp-card">
        <form method="GET" action="{{ route('admin.laporan.index') }}" id="form-filter-laporan">
            <input type="hidden" name="show" value="1">

            {{-- Header --}}
            <div class="lp-header">
                <div class="lp-header__left">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5" class="lp-header__icon">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                    </svg>
                    <span class="lp-header__label">Filter Laporan</span>
                </div>
                <a href="{{ route('admin.laporan.index') }}" class="lp-reset-link" title="Reset semua filter">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                        <path d="M3 3v5h5" />
                    </svg>
                    Reset
                </a>
            </div>

            {{-- Fields --}}
            <div class="lp-grid">

                {{-- ① Event --}}
                <div class="lp-field">
                    <label class="lp-label" for="lp-event">Pilih Event</label>
                    <div class="lp-select-wrap">
                        <svg class="lp-select-icon" width="13" height="13" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <path d="M16 2v4M8 2v4M3 10h18" />
                        </svg>
                        <select id="lp-event" name="event_id" class="lp-select">
                            <option value="">Semua Event</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}"
                                    data-mulai="{{ $event->tanggal_mulai->format('Y-m-d') }}"
                                    data-selesai="{{ $event->tanggal_selesai->format('Y-m-d') }}"
                                    {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                    {{ $event->nama_event }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="lp-select-chevron" width="13" height="13" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                </div>

                {{-- ② Status --}}
                <div class="lp-field">
                    <label class="lp-label" for="lp-status">Status</label>
                    <div class="lp-select-wrap">
                        <svg class="lp-select-icon" width="13" height="13" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 3" />
                        </svg>
                        <select id="lp-status" name="status" class="lp-select">
                            <option value="">Semua Status</option>
                            <option value="dititip" {{ request('status') === 'dititip' ? 'selected' : '' }}>Dititipkan
                            </option>
                            <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>
                                Terlambat</option>
                            <option value="sudah_diambil" {{ request('status') === 'sudah_diambil' ? 'selected' : '' }}>
                                Sudah Diambil</option>
                        </select>
                        <svg class="lp-select-chevron" width="13" height="13" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                </div>

                {{-- ③ Rentang Tanggal --}}
                <div class="lp-field lp-field--daterange">
                    <label class="lp-label">
                        Rentang Tanggal
                        <span class="lp-label__hint" id="lp-date-hint">otomatis dari event</span>
                    </label>
                    <div class="lp-date-row">
                        <div class="lp-input-wrap lp-input-wrap--flex">
                            <svg class="lp-input-icon" width="13" height="13" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <path d="M16 2v4M8 2v4M3 10h18" />
                            </svg>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                value="{{ request('tanggal_mulai') }}" class="lp-input lp-input--icon lp-input--date">
                        </div>
                        <span class="lp-date-sep">
                            <svg width="14" height="2" viewBox="0 0 14 2" fill="none">
                                <rect width="14" height="2" rx="1" fill="currentColor" />
                            </svg>
                        </span>
                        <div class="lp-input-wrap lp-input-wrap--flex">
                            <svg class="lp-input-icon" width="13" height="13" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <path d="M16 2v4M8 2v4M3 10h18" />
                            </svg>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                value="{{ request('tanggal_selesai') }}" class="lp-input lp-input--icon lp-input--date">
                        </div>
                    </div>
                </div>

                <div class="lp-field lp-field--action">
                    <label class="lp-label lp-label--spacer" aria-hidden="true">&nbsp;</label>
                    <button type="submit" class="lp-submit">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        Tampilkan Laporan
                    </button>
                </div>

            </div>

            {{-- Active chips --}}
            <div id="lp-chips-wrap" class="lp-chips-wrap">
                <div id="lp-chips" class="lp-chips"></div>
            </div>

        </form>
    </div>

    {{-- ─── Styles ─────────────────────────────────────────────────────── --}}
    <style>
        :root {
            --lp-navy-900: #0f2044;
            --lp-navy-800: #162d5c;
            --lp-blue-600: #1e4d8c;
            --lp-blue-400: #2d6cbf;
            --lp-blue-300: #6ea8e0;
            --lp-blue-200: #bfdbfe;
            --lp-blue-100: #dbeafe;
            --lp-blue-50: #eff6ff;
            --lp-teal-500: #0d9488;
            --lp-teal-100: #ccfbf1;
            --lp-teal-50: #f0fdfa;
            --lp-slate-700: #334155;
            --lp-slate-500: #64748b;
            --lp-slate-400: #94a3b8;
            --lp-slate-200: #e2e8f0;
            --lp-slate-100: #f1f5f9;
            --lp-text: #0f172a;
            --lp-radius-sm: 9px;
            --lp-radius: 12px;
            --lp-radius-lg: 18px;
        }

        /* ── Card ─────────────────────────────────── */
        .lp-card {
            background: #fff;
            border-radius: var(--lp-radius-lg);
            border: 1px solid var(--lp-slate-200);
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 18px rgba(0, 0, 0, .05);
            padding: 18px 20px 16px;
            margin-bottom: 20px;
        }

        /* ── Header ───────────────────────────────── */
        .lp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .lp-header__left {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .lp-header__icon {
            color: var(--lp-blue-600);
        }

        .lp-header__label {
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--lp-slate-700);
        }

        .lp-reset-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .75rem;
            font-weight: 600;
            color: var(--lp-slate-400);
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 8px;
            transition: background .15s, color .15s;
        }

        .lp-reset-link:hover {
            background: var(--lp-slate-100);
            color: var(--lp-slate-700);
        }

        /* ── Grid ─────────────────────────────────── */
        .lp-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            align-items: end;
        }

        @media (min-width: 768px) {
            .lp-grid {
                grid-template-columns: 1.3fr 1.1fr 2fr auto;
            }

            .lp-field--daterange {
                grid-column: span 1;
            }
        }

        /* Tombol di sebelah kanan */
        .lp-field--action {
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
        }

        .lp-field--action .lp-submit {
            width: auto;
            min-width: 200px;
        }

        /* ── Label ────────────────────────────────── */
        .lp-label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--lp-slate-500);
            margin-bottom: 7px;
            white-space: nowrap;
        }

        .lp-label__hint {
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            font-size: .68rem;
            color: var(--lp-slate-400);
            font-style: italic;
        }

        .lp-label--spacer {
            visibility: hidden;
        }

        /* ── Select ───────────────────────────────── */
        .lp-select-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .lp-select-icon {
            position: absolute;
            left: 11px;
            color: var(--lp-slate-400);
            pointer-events: none;
            z-index: 1;
        }

        .lp-select-chevron {
            position: absolute;
            right: 10px;
            color: var(--lp-slate-400);
            pointer-events: none;
            z-index: 1;
        }

        .lp-select {
            width: 100%;
            height: 38px;
            padding: 0 32px 0 30px;
            border-radius: var(--lp-radius-sm);
            border: 1.5px solid var(--lp-blue-100);
            background: var(--lp-blue-50);
            font-size: .82rem;
            color: var(--lp-text);
            outline: none;
            -webkit-appearance: none;
            appearance: none;
            cursor: pointer;
            transition: border-color .18s, box-shadow .18s;
        }

        .lp-select:focus {
            border-color: var(--lp-blue-400);
            box-shadow: 0 0 0 3px rgba(45, 108, 191, .13);
        }

        /* ── Input ────────────────────────────────── */
        .lp-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .lp-input-wrap--flex {
            flex: 1;
            min-width: 0;
        }

        .lp-input-icon {
            position: absolute;
            left: 11px;
            color: var(--lp-slate-400);
            pointer-events: none;
        }

        .lp-input {
            width: 100%;
            height: 38px;
            padding: 0 12px;
            border-radius: var(--lp-radius-sm);
            border: 1.5px solid var(--lp-blue-100);
            background: var(--lp-blue-50);
            font-size: .82rem;
            color: var(--lp-text);
            outline: none;
            -webkit-appearance: none;
            appearance: none;
            transition: border-color .18s, box-shadow .18s;
        }

        .lp-input--icon {
            padding-left: 32px;
        }

        .lp-input--date {
            font-size: .78rem;
        }

        .lp-input:focus {
            border-color: var(--lp-blue-400);
            box-shadow: 0 0 0 3px rgba(45, 108, 191, .13);
        }

        .lp-input::placeholder {
            color: var(--lp-slate-400);
        }

        /* ── Date row ─────────────────────────────── */
        .lp-date-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .lp-date-sep {
            color: var(--lp-slate-300);
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }

        /* ── Submit ───────────────────────────────── */
        .lp-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            width: auto;
            height: 38px;
            padding: 0 20px;
            border-radius: var(--lp-radius-sm);
            background: linear-gradient(135deg, var(--lp-navy-900), var(--lp-blue-600));
            color: #fff;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 0 3px 12px rgba(15, 32, 68, .22);
            transition: opacity .15s, transform .12s;
            white-space: nowrap;
        }

        .lp-submit:hover {
            opacity: .88;
        }

        .lp-submit:active {
            transform: scale(.97);
        }

        /* ── Active chips ─────────────────────────── */
        .lp-chips-wrap {
            margin-top: 12px;
            padding-top: 11px;
            border-top: 1px solid var(--lp-slate-100);
        }

        .lp-chips-wrap:empty,
        .lp-chips-wrap:has(.lp-chips:empty) {
            display: none;
        }

        .lp-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .lp-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            background: var(--lp-blue-100);
            color: var(--lp-blue-600);
            white-space: nowrap;
            animation: lpChipIn .2s ease both;
        }

        @keyframes lpChipIn {
            from {
                opacity: 0;
                transform: scale(.85);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* ── Mobile responsive ────────────────────── */
        @media (max-width: 767px) {
            .lp-input-wrap--flex .lp-input-icon {
                display: none;
            }

            .lp-input--icon {
                padding-left: 12px;
                padding-right: 6px;
                font-size: 0.74rem;
            }

            .lp-input--date::-webkit-calendar-picker-indicator {
                margin-left: 2px;
                padding: 0;
                width: 16px;
                height: 16px;
            }

            .lp-card {
                padding: 16px 14px 14px;
            }

            .lp-label--spacer {
                display: none;
            }

            .lp-field--action {
                justify-content: stretch;
            }

            .lp-field--action .lp-submit {
                width: 100%;
                min-width: 0;
            }

            .lp-input-wrap--flex {
                flex: 1 1 0%;
                min-width: 0;
            }
        }
    </style>

    {{-- ─── Script ─────────────────────────────────────────────────────── --}}
    <script>
        (function() {
            const eventSel = document.getElementById('lp-event');
            const tglMulai = document.getElementById('tanggal_mulai');
            const tglSelesai = document.getElementById('tanggal_selesai');
            const dateHint = document.getElementById('lp-date-hint');
            const chipsEl = document.getElementById('lp-chips');
            const chipsWrap = document.getElementById('lp-chips-wrap');

            /* Auto-fill dates when event selected */
            if (eventSel) {
                eventSel.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    const mulai = opt.dataset.mulai;
                    const selesai = opt.dataset.selesai;
                    if (mulai && selesai) {
                        tglMulai.value = mulai;
                        tglSelesai.value = selesai;
                        if (dateHint) {
                            dateHint.textContent = 'terisi otomatis';
                            dateHint.style.color = 'var(--lp-blue-400)';
                            setTimeout(() => {
                                dateHint.textContent = 'otomatis dari event';
                                dateHint.style.color = '';
                            }, 2000);
                        }
                    } else {
                        tglMulai.value = tglSelesai.value = '';
                    }
                });
            }

            /* Active filter chips */
            const params = new URLSearchParams(window.location.search);
            const labels = {
                event_id: 'Event',
                status: 'Status',
                tanggal_mulai: 'Dari',
                tanggal_selesai: 'S/d',
            };
            const statusMap = {
                dititip: 'Dititipkan',
                terlambat: 'Terlambat',
                sudah_diambil: 'Sudah Diambil',
            };

            let hasChip = false;
            if (chipsEl) {
                params.forEach((val, key) => {
                    if (!val || !labels[key]) return;
                    let display = val;
                    if (key === 'status') display = statusMap[val] ?? val;
                    if (key === 'event_id') {
                        const opt = eventSel?.querySelector(`option[value="${val}"]`);
                        display = opt ? opt.textContent.trim() : val;
                    }
                    const chip = document.createElement('span');
                    chip.className = 'lp-chip';
                    chip.innerHTML = `<span style="opacity:.6">${labels[key]}:</span> ${display}`;
                    chipsEl.appendChild(chip);
                    hasChip = true;
                });
                if (!hasChip && chipsWrap) chipsWrap.style.display = 'none';
            }
        })();
    </script>

    {{-- Summary Cards --}}
    @if ($transaksis->count() > 0)
        <div class="anim-fade-up delay-3 grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
            <div class="bg-white rounded-2xl p-5 border border-gray-100" style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                        style="background: linear-gradient(135deg, #eff6ff, #dbeafe)">
                        <span class="text-base">📋</span>
                    </div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Transaksi</p>
                </div>
                <p class="text-3xl font-black text-gray-900">{{ $transaksis->count() }}</p>
                <p class="text-xs text-gray-400 mt-1">transaksi ditemukan</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100" style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                        style="background: linear-gradient(135deg, #f0fdf4, #dcfce7)">
                        <span class="text-base">💰</span>
                    </div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Pendapatan</p>
                </div>
                <p class="text-2xl font-black" style="color: #0f2044">
                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                </p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100" style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                        style="background: linear-gradient(135deg, #fff7ed, #fed7aa)">
                        <span class="text-base">📦</span>
                    </div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Dititip / Terlambat / Diambil
                    </p>
                </div>
                <p class="text-2xl font-black text-gray-900">
                    <span style="color: #7c3aed">{{ $totalDititip }}</span>
                    <span class="text-gray-300 mx-1 text-lg">/</span>
                    <span style="color: #dc2626">{{ $totalTerlambat }}</span>
                    <span class="text-gray-300 mx-1 text-lg">/</span>
                    <span style="color: #15803d">{{ $totalDiambil }}</span>
                </p>
            </div>
        </div>
    @endif

    {{-- Tabel --}}
    <div class="anim-fade-up delay-4 bg-white rounded-2xl border border-gray-100 overflow-hidden"
        style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
        <table id="tabel-laporan" class="w-full text-sm" style="width:100%">
            <thead>
                <tr style="background: #f8faff; border-bottom: 2px solid #e2e8f0">
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        No. Transaksi
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Penitip
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Barang & Ukuran
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Kasir
                    </th>
                    <th class="px-5 py-4 text-right whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Total
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Metode Bayar
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Status
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Waktu
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $t)
                    <tr class="table-row border-t border-gray-50">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <a href="{{ route('admin.transaksis.show', $t) }}"
                                class="font-bold hover:underline transition"
                                style="color: #1a3a6b; font-family: monospace; font-size: 12px">
                                {{ $t->nomor_transaksi }}
                            </a>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white flex-shrink-0"
                                    style="background: linear-gradient(135deg, #0f2044, #4a9eff); font-size: 11px">
                                    {{ strtoupper(substr($t->nama_penitip, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm whitespace-nowrap">
                                        {{ $t->nama_penitip }}</p>
                                    <p class="text-gray-400" style="font-size: 11px">{{ $t->no_whatsapp }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @foreach ($t->details->take(1) as $d)
                                <p class="font-medium text-gray-700 text-sm whitespace-nowrap">
                                    {{ $d->jenis_barang_string }}
                                </p>
                                <span class="inline-block px-2 py-0.5 rounded-md text-xs font-bold mt-0.5"
                                    style="background: #eff6ff; color: #1d4ed8">
                                    Ukuran {{ $d->ukuran }}
                                </span>
                            @endforeach
                            @if ($t->details->count() > 1)
                                <p class="text-xs mt-0.5" style="color: #1a3a6b">
                                    +{{ $t->details->count() - 1 }} barang lain
                                </p>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-white flex-shrink-0"
                                    style="background: #1a3a6b; font-size: 9px">
                                    {{ strtoupper(substr($t->kasir->name, 0, 1)) }}
                                </div>
                                <span class="text-gray-500 text-xs">{{ $t->kasir->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <span class="font-black text-gray-900 text-sm">
                                Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            @php
                                $metodeBadge = [
                                    'tunai' => ['label' => 'Tunai', 'bg' => '#f0fdf4', 'color' => '#15803d'],
                                    'transfer' => ['label' => 'Transfer', 'bg' => '#eff6ff', 'color' => '#1d4ed8'],
                                    'qris' => ['label' => 'QRIS', 'bg' => '#faf5ff', 'color' => '#7c3aed'],
                                ];
                                $m = $metodeBadge[$t->metode_bayar] ?? [
                                    'label' => ucfirst($t->metode_bayar ?? '-'),
                                    'bg' => '#f1f5f9',
                                    'color' => '#64748b',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold"
                                style="background: {{ $m['bg'] }}; color: {{ $m['color'] }}">
                                {{ $m['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 rounded-full text-xs font-bold"
                                style="background: {{ $t->status === 'dititip' ? '#faf5ff' : ($t->status === 'terlambat' ? '#fff5f5' : '#f0fdf4') }};
                               color: {{ $t->status === 'dititip' ? '#7c3aed' : ($t->status === 'terlambat' ? '#dc2626' : '#15803d') }}">
                                {{ $t->status === 'dititip' ? 'DITITIPKAN' : ($t->status === 'terlambat' ? 'TERLAMBAT' : 'DIAMBIL') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-gray-400 text-xs">
                            {{ $t->waktu_penitipan->format('d M Y') }}<br>
                            {{ $t->waktu_penitipan->format('H:i') }} WIB
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl"
                                    style="background: #f8faff">📋</div>
                                <p class="font-semibold text-gray-400">
                                    @if (request()->has('show'))
                                        Tidak ada data yang sesuai filter.
                                    @else
                                        Pilih filter di atas untuk menampilkan laporan.
                                    @endif
                                </p>
                                <p class="text-xs text-gray-300">Pilih event atau tanggal untuk memulai</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <p class="text-center text-xs text-gray-300 mt-8">
        © {{ date('Y') }} Vendor Savve — Storage Management System
    </p>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            @if ($transaksis->count() > 0)
                $('#tabel-laporan').DataTable({
                    responsive: false,
                    scrollX: true,
                    pageLength: 15,
                    language: {
                        search: "🔍",
                        searchPlaceholder: "Cari laporan...",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_–_END_ dari _TOTAL_ transaksi",
                        infoEmpty: "Tidak ada data",
                        paginate: {
                            previous: "‹",
                            next: "›"
                        },
                        zeroRecords: "Tidak ada data yang cocok",
                        emptyTable: "Belum ada data laporan"
                    },
                    dom: '<"flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 px-5 py-4"f>rtip',
                    order: [
                        [7, 'desc']
                    ],
                    columnDefs: [{
                        orderable: false,
                        targets: [2]
                    }]
                });
            @endif
        });
    </script>
@endpush
