@extends('layouts.admin')
@section('title', 'Kelola Event')

@section('content')

    {{-- Header --}}
    <div class="anim-fade-up delay-1 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #1a3a6b">Management</p>
            <h1 class="text-xl lg:text-2xl font-black text-gray-900">Kelola Event</h1>
            <p class="text-gray-400 text-sm mt-1">Kelola data event, tarif, dan lihat rekap performa.</p>
        </div>
        <a href="{{ route('admin.events.create') }}"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-bold text-sm transition hover:opacity-90 self-start flex-shrink-0"
            style="background: linear-gradient(135deg, #0f2044, #1e4d8c); box-shadow: 0 4px 12px rgba(15,32,68,0.2)">
            ＋ Tambah Event
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="anim-fade-up delay-2 grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 lg:p-5 border border-gray-100" style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background: linear-gradient(135deg, #f0fdf4, #dcfce7)">
                    <span class="text-base">🟢</span>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider" style="font-size: 9px">Event Aktif
                </p>
            </div>
            <p class="text-2xl lg:text-3xl font-black text-gray-900">{{ $totalEventAktif }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 lg:p-5 border border-gray-100" style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background: linear-gradient(135deg, #f8faff, #e2e8f0)">
                    <span class="text-base">⚫</span>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider" style="font-size: 9px">Event Selesai
                </p>
            </div>
            <p class="text-2xl lg:text-3xl font-black text-gray-900">{{ $totalEventSelesai }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 lg:p-5 border border-gray-100" style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background: linear-gradient(135deg, #eff6ff, #dbeafe)">
                    <span class="text-base">📋</span>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider" style="font-size: 9px">Total
                    Transaksi</p>
            </div>
            <p class="text-2xl lg:text-3xl font-black text-gray-900">{{ number_format($totalTransaksi) }}</p>
        </div>
        <div class="rounded-2xl p-4 lg:p-5 text-white relative overflow-hidden"
            style="background: linear-gradient(135deg, #0f2044, #1a3a6b); box-shadow: 0 8px 24px rgba(15,32,68,0.2)">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background: rgba(255,255,255,0.15)">
                    <span class="text-base">💰</span>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider"
                    style="color: rgba(255,255,255,0.6); font-size: 9px">Total Pendapatan</p>
            </div>
            <p class="text-lg lg:text-xl font-black">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            <div class="absolute -bottom-3 -right-3 w-16 h-16 rounded-full" style="background: rgba(255,255,255,0.05)">
            </div>
        </div>
    </div>

    {{-- Tabel Event --}}
    <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 overflow-hidden"
        style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">

        {{-- Tab Filter --}}
        <div class="flex items-center gap-1 px-5 py-4 border-b border-gray-100 overflow-x-auto">
            <a href="{{ route('admin.events.index') }}"
                class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex-shrink-0"
                style="{{ !request('filter') ? 'background: #eff6ff; color: #1d4ed8' : 'color: #94a3b8' }}">
                Semua ({{ $totalEventAktif + $totalEventSelesai }})
            </a>
            <a href="{{ route('admin.events.index', ['filter' => 'aktif']) }}"
                class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex-shrink-0"
                style="{{ request('filter') === 'aktif' ? 'background: #f0fdf4; color: #15803d' : 'color: #94a3b8' }}">
                ● Aktif ({{ $totalEventAktif }})
            </a>
            <a href="{{ route('admin.events.index', ['filter' => 'nonaktif']) }}"
                class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex-shrink-0"
                style="{{ request('filter') === 'nonaktif' ? 'background: #f8faff; color: #64748b' : 'color: #94a3b8' }}">
                ● Selesai ({{ $totalEventSelesai }})
            </a>
        </div>

        <table id="tabel-event" class="w-full text-sm" style="width:100%">
            <thead>
                <tr style="background: #f8faff; border-bottom: 2px solid #e2e8f0">
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Nama Event</th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Kode Event</th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Tanggal</th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Tarif S/M/L/XL/Gadget</th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Status</th>
                    <th class="px-5 py-4 text-right whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Transaksi</th>
                    <th class="px-5 py-4 text-right whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Pendapatan</th>
                    <th class="px-5 py-4 text-center whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                    @php
                        $transaksis = $event->transaksis;
                        $pendapatan = $transaksis->sum(fn($t) => $t->total_harga);
                        $dititip = $transaksis->where('status', 'dititip')->count();
                        $terlambat = $transaksis->where('status', 'terlambat')->count();
                        $diambil = $transaksis->where('status', 'sudah_diambil')->count();
                        $tarifs = $event->tarifs->keyBy('ukuran');
                    @endphp
                    <tr class="table-row" style="border-top: 1px solid #f1f5f9">
                        <td class="px-5 py-4">
                            <p class="font-bold text-gray-800">{{ $event->nama_event }}</p>
                            @if ($terlambat > 0)
                                <p class="text-xs font-semibold mt-0.5" style="color: #dc2626">
                                    ⚠ {{ $terlambat }} terlambat diambil
                                </p>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 rounded-lg text-xs font-black font-mono"
                                style="background: #eff6ff; color: #1d4ed8">
                                {{ $event->kode_event ?? '-' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <p class="text-xs text-gray-600">{{ $event->tanggal_mulai->format('d M Y') }}</p>
                            <p class="text-xs text-gray-400">s/d {{ $event->tanggal_selesai->format('d M Y') }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex gap-1 flex-wrap">
                                @foreach (['S', 'M', 'L', 'XL', 'Gadget'] as $u)
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-bold whitespace-nowrap"
                                        style="background: #eff6ff; color: #1d4ed8">
                                        {{ $u }}: Rp {{ number_format($tarifs[$u]->harga ?? 0, 0, ',', '.') }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="px-3 py-1.5 rounded-full text-xs font-bold"
                                style="background: {{ $event->status === 'aktif' ? '#f0fdf4' : '#f8faff' }};
                                       color: {{ $event->status === 'aktif' ? '#15803d' : '#94a3b8' }}">
                                {{ $event->status === 'aktif' ? '● aktif' : '● selesai' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <p class="font-black text-gray-800">{{ $transaksis->count() }}</p>
                            <div class="flex justify-end gap-1 mt-1">
                                @if ($dititip > 0)
                                    <span class="text-xs px-1.5 py-0.5 rounded font-bold"
                                        style="background: #faf5ff; color: #7c3aed">{{ $dititip }}</span>
                                @endif
                                @if ($terlambat > 0)
                                    <span class="text-xs px-1.5 py-0.5 rounded font-bold"
                                        style="background: #fff5f5; color: #dc2626">{{ $terlambat }}</span>
                                @endif
                                @if ($diambil > 0)
                                    <span class="text-xs px-1.5 py-0.5 rounded font-bold"
                                        style="background: #f0fdf4; color: #15803d">{{ $diambil }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <span class="font-black" style="color: #0f2044; font-size: 13px">
                                Rp {{ number_format($pendapatan, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.events.rekap', $event) }}"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition hover:opacity-80"
                                    style="background: #eff6ff; color: #1d4ed8">
                                    📊 Rekap
                                </a>
                                <a href="{{ route('admin.events.edit', $event) }}"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition hover:opacity-80"
                                    style="background: #f8faff; color: #1a3a6b; border: 1px solid #e2e8f0">
                                    Edit
                                </a>
                                <button type="button"
                                    onclick="hapusEvent('{{ route('admin.events.destroy', $event) }}')"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition hover:opacity-80"
                                    style="background: #fff5f5; color: #dc2626; border: 1px solid #fecaca">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-8 text-center text-gray-400 text-sm">
                            Belum ada event yang tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Form hapus tersembunyi, di luar tabel tapi masih dalam container --}}
        <form id="form-hapus" method="POST" style="display:none">
            @csrf
            @method('DELETE')
        </form>

    </div>

@endsection

@push('scripts')
    <script>
        function hapusEvent(url) {
            if (!confirm('Hapus event ini?')) return;
            const form = document.getElementById('form-hapus');
            form.action = url;
            form.submit();
        }

        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#tabel-event')) {
                $('#tabel-event').DataTable().destroy();
            }

            $('#tabel-event').DataTable({
                responsive: false,
                scrollX: true,
                pageLength: 10,
                language: {
                    search: "🔍",
                    searchPlaceholder: "Cari event...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_–_END_ dari _TOTAL_ event",
                    infoEmpty: "Tidak ada data",
                    paginate: {
                        previous: "‹",
                        next: "›"
                    },
                    zeroRecords: "Tidak ada event yang cocok",
                    emptyTable: "Belum ada event"
                },
                dom: '<"flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 px-5 py-4"f>rtip',
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [2, 3, 5, 6, 7]
                }]
            });
        });
    </script>
@endpush
