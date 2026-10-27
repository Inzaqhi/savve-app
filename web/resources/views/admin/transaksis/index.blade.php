@extends('layouts.admin')
@section('title', 'Data Transaksi')

@section('content')

    <div class="anim-fade-up delay-1 mb-6">
        <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #1a3a6b">Laporan</p>
        <h1 class="text-xl lg:text-2xl font-black text-gray-900">Data Transaksi</h1>
        <p class="text-gray-400 text-sm mt-1">Monitor seluruh transaksi penitipan barang dari semua kasir.</p>
    </div>

    {{-- ─── Filter Riwayat Transaksi ──────────────────────────────────────── --}}
    <div class="ft-filter-card">
        <form method="GET" action="{{ route('admin.transaksis.index') }}" id="form-filter">

            {{-- Header row --}}
            <div class="ft-filter-header">
                <div class="ft-filter-header__left">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5" style="color:var(--ft-blue-600)">
                        <path d="M3 6h18M7 12h10M11 18h2" />
                    </svg>
                    <span class="ft-filter-header__label">Filter</span>
                </div>
                <a href="{{ route('admin.transaksis.index') }}" class="ft-reset-link" title="Reset semua filter">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                        <path d="M3 3v5h5" />
                    </svg>
                    Reset
                </a>
            </div>

            {{-- Fields grid --}}
            <div class="ft-grid">

                {{-- ① Cari --}}
                <div class="ft-field ft-field--wide">
                    <label class="ft-label" for="ft-search">Cari Penitip / No. Transaksi</label>
                    <div class="ft-input-wrap">
                        <svg class="ft-input-icon" width="14" height="14" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input id="ft-search" type="text" name="search" value="{{ request('search') }}"
                            class="ft-input ft-input--with-icon" placeholder="Nama atau nomor transaksi…"
                            autocomplete="off">
                    </div>
                </div>

                {{-- ② Event --}}
                <div class="ft-field">
                    <label class="ft-label" for="ft-event">Event</label>
                    <div class="ft-select-wrap">
                        <svg class="ft-select-icon" width="13" height="13" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <path d="M16 2v4M8 2v4M3 10h18" />
                        </svg>
                        <select id="ft-event" name="event_id" class="ft-select">
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
                        <svg class="ft-select-chevron" width="13" height="13" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                </div>

                {{-- ③ Status --}}
                <div class="ft-field">
                    <label class="ft-label" for="ft-status">Status</label>
                    <div class="ft-select-wrap">
                        <svg class="ft-select-icon" width="13" height="13" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 3" />
                        </svg>
                        <select id="ft-status" name="status" class="ft-select">
                            <option value="">Semua Status</option>
                            <option value="dititip" {{ request('status') === 'dititip' ? 'selected' : '' }}>Dititipkan
                            </option>
                            <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>
                                Terlambat</option>
                            <option value="sudah_diambil" {{ request('status') === 'sudah_diambil' ? 'selected' : '' }}>
                                Sudah Diambil</option>
                        </select>
                        <svg class="ft-select-chevron" width="13" height="13" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                </div>

                {{-- ④ Rentang Tanggal --}}
                <div class="ft-field ft-field--date-range">
                    <label class="ft-label">
                        Rentang Tanggal
                        <span class="ft-label__hint" id="ft-date-hint">otomatis dari event</span>
                    </label>
                    <div class="ft-date-row">
                        <div class="ft-input-wrap ft-input-wrap--date">
                            <svg class="ft-input-icon" width="13" height="13" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <path d="M16 2v4M8 2v4M3 10h18" />
                            </svg>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                value="{{ request('tanggal_mulai') }}"
                                class="ft-input ft-input--date ft-input--with-icon" placeholder="Mulai">
                        </div>
                        <span class="ft-date-sep">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path d="M5 12h14" />
                            </svg>
                        </span>
                        <div class="ft-input-wrap ft-input-wrap--date">
                            <svg class="ft-input-icon" width="13" height="13" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <path d="M16 2v4M8 2v4M3 10h18" />
                            </svg>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                value="{{ request('tanggal_selesai') }}"
                                class="ft-input ft-input--date ft-input--with-icon" placeholder="Selesai">
                        </div>
                    </div>
                </div>

            </div>

            {{-- Active filter chips + submit --}}
            <div class="ft-footer">
                {{-- Active chips (rendered by JS) --}}
                <div id="ft-chips" class="ft-chips"></div>

                <button type="submit" class="ft-submit-btn">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    Terapkan Filter
                </button>
            </div>

        </form>
    </div>

    {{-- ─── Styles ────────────────────────────────────────────────────────── --}}
    <style>
        /* Tokens */
        :root {
            --ft-navy-900: #0f2044;
            --ft-navy-800: #162d5c;
            --ft-blue-600: #1e4d8c;
            --ft-blue-500: #2d6cbf;
            --ft-blue-300: #6ea8e0;
            --ft-blue-200: #bfdbfe;
            --ft-blue-100: #dbeafe;
            --ft-blue-50: #eff6ff;

            --ft-slate-700: #334155;
            --ft-slate-500: #64748b;
            --ft-slate-400: #94a3b8;
            --ft-slate-200: #e2e8f0;
            --ft-slate-100: #f1f5f9;

            --ft-text: #0f172a;
            --ft-radius: 12px;
            --ft-radius-sm: 9px;
        }

        /* Card */
        .ft-filter-card {
            background: #fff;
            border-radius: 18px;
            border: 1px solid var(--ft-slate-200);
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 18px rgba(0, 0, 0, .05);
            padding: 18px 20px 16px;
            margin-bottom: 20px;
        }

        /* Header row */
        .ft-filter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .ft-filter-header__left {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .ft-filter-header__label {
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--ft-slate-700);
        }

        .ft-reset-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .75rem;
            font-weight: 600;
            color: var(--ft-slate-400);
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 8px;
            transition: background .15s, color .15s;
        }

        .ft-reset-link:hover {
            background: var(--ft-slate-100);
            color: var(--ft-slate-700);
        }

        /* Grid */
        .ft-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }

        @media (min-width: 768px) {
            .ft-grid {
                grid-template-columns: 2fr 1.2fr 1.2fr 2fr;
                align-items: end;
            }
        }

        .ft-field--wide {
            grid-column: span 2;
        }

        .ft-field--date-range {
            grid-column: span 2;
        }

        @media (min-width: 768px) {
            .ft-field--wide {
                grid-column: span 1;
            }

            .ft-field--date-range {
                grid-column: span 1;
            }
        }

        /* Label */
        .ft-label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--ft-slate-500);
            margin-bottom: 7px;
            white-space: nowrap;
        }

        .ft-label__hint {
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            font-size: .68rem;
            color: var(--ft-slate-400);
            font-style: italic;
        }

        /* Input wrap */
        .ft-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .ft-input-wrap--date {
            flex: 1;
            min-width: 0;
        }

        .ft-input-icon {
            position: absolute;
            left: 11px;
            color: var(--ft-slate-400);
            pointer-events: none;
            flex-shrink: 0;
        }

        /* Input */
        .ft-input {
            width: 100%;
            height: 38px;
            padding: 0 12px;
            border-radius: var(--ft-radius-sm);
            border: 1.5px solid var(--ft-blue-100);
            background: var(--ft-blue-50);
            font-size: .82rem;
            color: var(--ft-text);
            transition: border-color .18s, box-shadow .18s;
            outline: none;
            -webkit-appearance: none;
            appearance: none;
        }

        .ft-input--with-icon {
            padding-left: 32px;
        }

        .ft-input--date {
            font-size: .78rem;
        }

        .ft-input:focus {
            border-color: var(--ft-blue-500);
            box-shadow: 0 0 0 3px rgba(45, 108, 191, .15);
        }

        .ft-input::placeholder {
            color: var(--ft-slate-400);
        }

        /* Select wrap */
        .ft-select-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .ft-select-icon {
            position: absolute;
            left: 11px;
            color: var(--ft-slate-400);
            pointer-events: none;
            z-index: 1;
        }

        .ft-select-chevron {
            position: absolute;
            right: 10px;
            color: var(--ft-slate-400);
            pointer-events: none;
            z-index: 1;
        }

        .ft-select {
            width: 100%;
            height: 38px;
            padding: 0 32px 0 30px;
            border-radius: var(--ft-radius-sm);
            border: 1.5px solid var(--ft-blue-100);
            background: var(--ft-blue-50);
            font-size: .82rem;
            color: var(--ft-text);
            transition: border-color .18s, box-shadow .18s;
            outline: none;
            -webkit-appearance: none;
            appearance: none;
            cursor: pointer;
        }

        .ft-select:focus {
            border-color: var(--ft-blue-500);
            box-shadow: 0 0 0 3px rgba(45, 108, 191, .15);
        }

        /* Date range row */
        .ft-date-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ft-date-sep {
            color: var(--ft-slate-300);
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }

        /* Footer */
        .ft-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-top: 12px;
            border-top: 1px solid var(--ft-slate-100);
            flex-wrap: wrap;
        }

        /* Chips */
        .ft-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            flex: 1;
            min-width: 0;
        }

        .ft-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            background: var(--ft-blue-100);
            color: var(--ft-blue-600);
            white-space: nowrap;
            animation: ftChipIn .2s ease both;
        }

        @keyframes ftChipIn {
            from {
                opacity: 0;
                transform: scale(.85);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Submit */
        .ft-submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            border-radius: var(--ft-radius-sm);
            background: linear-gradient(135deg,
                    var(--ft-navy-900),
                    var(--ft-blue-600));
            color: #fff;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 0 3px 12px rgba(15, 32, 68, .22);
            transition: opacity .15s, transform .12s;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .ft-submit-btn:hover {
            opacity: .88;
        }

        .ft-submit-btn:active {
            transform: scale(.97);
        }

        /* Aksi buttons */
        .ft-aksi-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
            transition: opacity .15s, transform .12s;
        }

        .ft-aksi-btn:hover {
            opacity: .8;
        }

        .ft-aksi-btn:active {
            transform: scale(.94);
        }
    </style>

    {{-- ─── Script ────────────────────────────────────────────────────────── --}}
    <script>
        (function() {
            /* Auto-fill dates when event is selected */
            const eventSel = document.getElementById('ft-event');
            const tglMulai = document.getElementById('tanggal_mulai');
            const tglSelesai = document.getElementById('tanggal_selesai');
            const dateHint = document.getElementById('ft-date-hint');

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
                            dateHint.style.color = 'var(--ft-blue-600)';
                            setTimeout(() => {
                                dateHint.textContent = 'otomatis dari event';
                                dateHint.style.color = '';
                            }, 2000);
                        }
                    } else {
                        tglMulai.value = '';
                        tglSelesai.value = '';
                        if (dateHint) dateHint.textContent = 'otomatis dari event';
                    }
                });
            }

            /* Active-filter chips */
            const chips = document.getElementById('ft-chips');
            const params = new URLSearchParams(window.location.search);
            const labels = {
                search: 'Cari',
                event_id: 'Event',
                status: 'Status',
                tanggal_mulai: 'Dari',
                tanggal_selesai: 'S/d',
            };
            const statusLabels = {
                dititip: 'Dititipkan',
                terlambat: 'Terlambat',
                sudah_diambil: 'Sudah Diambil',
            };

            if (chips) {
                let hasChip = false;
                params.forEach((val, key) => {
                    if (!val || !labels[key]) return;
                    let display = val;
                    if (key === 'status') display = statusLabels[val] ?? val;
                    if (key === 'event_id') {
                        const opt = eventSel?.querySelector(`option[value="${val}"]`);
                        display = opt ? opt.textContent.trim() : val;
                    }
                    const chip = document.createElement('span');
                    chip.className = 'ft-chip';
                    chip.innerHTML = `<span style="opacity:.65">${labels[key]}:</span> ${display}`;
                    chips.appendChild(chip);
                    hasChip = true;
                });
            }
        })();
    </script>

    {{-- Tabel --}}
    <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 overflow-hidden"
        style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
        <table id="tabel-transaksi" class="w-full text-sm" style="width:100%">
            <thead>
                <tr style="background: #f8faff; border-bottom: 2px solid var(--ft-slate-200)">
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:var(--ft-slate-400);text-transform:uppercase;letter-spacing:0.06em">
                        No. Transaksi
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:var(--ft-slate-400);text-transform:uppercase;letter-spacing:0.06em">
                        Penitip
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:var(--ft-slate-400);text-transform:uppercase;letter-spacing:0.06em">
                        Barang
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:var(--ft-slate-400);text-transform:uppercase;letter-spacing:0.06em">
                        Event
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:var(--ft-slate-400);text-transform:uppercase;letter-spacing:0.06em">
                        Kasir
                    </th>
                    <th class="px-5 py-4 text-right whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:var(--ft-slate-400);text-transform:uppercase;letter-spacing:0.06em">
                        Total
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:var(--ft-slate-400);text-transform:uppercase;letter-spacing:0.06em">
                        Metode Bayar
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:var(--ft-slate-400);text-transform:uppercase;letter-spacing:0.06em">
                        Status
                    </th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:var(--ft-slate-400);text-transform:uppercase;letter-spacing:0.06em">
                        Waktu
                    </th>
                    <th class="px-5 py-4 text-center whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:var(--ft-slate-400);text-transform:uppercase;letter-spacing:0.06em">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $t)
                    <tr class="table-row" style="border-top: 1px solid var(--ft-slate-100)">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <a href="{{ route('admin.transaksis.show', $t) }}" class="font-bold hover:underline"
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
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($t->details as $d)
                                    <span
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-bold"
                                        style="background: var(--ft-blue-50); color: #1d4ed8">
                                        {{ $d->ukuran }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-500 text-xs whitespace-nowrap">
                            {{ Str::limit($t->event->nama_event, 15) }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-white flex-shrink-0"
                                    style="background: #1a3a6b; font-size: 9px">
                                    {{ strtoupper(substr($t->kasir->name, 0, 1)) }}
                                </div>
                                <span class="text-xs text-gray-500 whitespace-nowrap">
                                    {{ Str::limit($t->kasir->name, 10) }}
                                </span>
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
                                    'Cash' => [
                                        'label' => 'Cash',
                                        'icon' => '💵',
                                        'bg' => '#f0fdf4',
                                        'color' => '#15803d',
                                    ],
                                    'QRIS' => [
                                        'label' => 'QRIS',
                                        'icon' => '📱',
                                        'bg' => '#faf5ff',
                                        'color' => '#7c3aed',
                                    ],
                                    'Online' => [
                                        'label' => 'Online',
                                        'icon' => '🌐',
                                        'bg' => 'var(--ft-blue-50)',
                                        'color' => '#1d4ed8',
                                    ],
                                ];
                                $m = $metodeBadge[$t->metode_bayar] ?? [
                                    'label' => $t->metode_bayar ?? '-',
                                    'icon' => '',
                                    'bg' => 'var(--ft-slate-100)',
                                    'color' => 'var(--ft-slate-500)',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold"
                                style="background: {{ $m['bg'] }}; color: {{ $m['color'] }}">
                                {{ $m['icon'] }} {{ $m['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 rounded-full text-xs font-bold"
                                style="background: {{ $t->status === 'dititip' ? 'var(--ft-blue-50)' : ($t->status === 'terlambat' ? '#fff5f5' : '#f0fdf4') }};
                               color: {{ $t->status === 'dititip' ? '#1d4ed8' : ($t->status === 'terlambat' ? '#dc2626' : '#15803d') }}">
                                {{ $t->status === 'dititip' ? 'DITITIPKAN' : ($t->status === 'terlambat' ? 'TERLAMBAT' : 'SUDAH DIAMBIL') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-gray-400 text-xs">
                            {{ $t->waktu_penitipan->format('d M Y') }}<br>
                            {{ $t->waktu_penitipan->format('H:i') }} WIB
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.transaksis.show', $t) }}" class="ft-aksi-btn"
                                    style="background: var(--ft-blue-50); color: var(--ft-blue-600)" title="Lihat Detail">
                                    👁️
                                </a>
                                <button type="button" class="ft-aksi-btn btn-hapus-transaksi"
                                    style="background: #fff5f5; color: #dc2626" title="Hapus Transaksi"
                                    data-url="{{ route('admin.transaksis.destroy', $t) }}"
                                    data-nomor="{{ $t->nomor_transaksi }}" data-nama="{{ $t->nama_penitip }}">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl"
                                    style="background: #f8faff">📋</div>
                                <p class="font-semibold text-gray-400">Belum ada transaksi.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Konfirmasi Hapus Transaksi --}}
    <div id="modal-hapus-transaksi" class="hidden fixed inset-0 z-50"
        style="background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; backdrop-filter: blur(2px);">

        <div class="anim-scale-in"
            style="background: white; border-radius: 20px; width: 100%; max-width: 380px; margin: 0 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">

            <div class="px-6 pt-6 pb-4 text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4"
                    style="background: #fff5f5">
                    🗑️
                </div>
                <h3 class="font-black text-gray-900 text-lg mb-1">Hapus Transaksi Ini?</h3>
                <p class="text-sm text-gray-400">
                    Transaksi <span id="modal-hapus-nomor" class="font-bold font-mono text-gray-700"></span>
                    milik <span id="modal-hapus-nama" class="font-bold text-gray-700"></span>
                    akan dihapus permanen beserta semua detail barangnya. Aksi ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="px-6 pb-6 flex gap-3">
                <button type="button" onclick="tutupModalHapusTransaksi()"
                    class="flex-1 py-3 rounded-xl font-bold text-sm transition"
                    style="background: #f1f5f9; color: #64748b">
                    Batal
                </button>
                <form id="form-hapus-transaksi" method="POST" class="flex-1" action="">
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

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#tabel-transaksi').DataTable({
                responsive: false,
                scrollX: true,
                pageLength: 15,
                language: {
                    search: "🔍",
                    searchPlaceholder: "Cari transaksi...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_–_END_ dari _TOTAL_ transaksi",
                    infoEmpty: "Tidak ada data",
                    paginate: {
                        previous: "‹",
                        next: "›"
                    },
                    zeroRecords: "Tidak ada transaksi yang cocok",
                    emptyTable: "Belum ada transaksi"
                },
                dom: '<"flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 px-5 py-4"f>rtip',
                order: [
                    [8, 'desc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [2, 9]
                }]
            });

            // ── Modal konfirmasi hapus (delegated, aman untuk pagination DataTables) ──
            $(document).on('click', '.btn-hapus-transaksi', function() {
                $('#form-hapus-transaksi').attr('action', $(this).data('url'));
                $('#modal-hapus-nomor').text($(this).data('nomor'));
                $('#modal-hapus-nama').text($(this).data('nama'));
                bukaModalHapusTransaksi();
            });
        });

        function bukaModalHapusTransaksi() {
            const modal = document.getElementById('modal-hapus-transaksi');
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }

        function tutupModalHapusTransaksi() {
            const modal = document.getElementById('modal-hapus-transaksi');
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }

        document.getElementById('modal-hapus-transaksi').addEventListener('click', function(e) {
            if (e.target === this) tutupModalHapusTransaksi();
        });
    </script>
@endpush
