@extends('layouts.admin')
@section('title', 'Kelola Kasir')

@section('content')

    <div class="anim-fade-up delay-1 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #1a3a6b">Management</p>
            <h1 class="text-xl lg:text-2xl font-black text-gray-900">Kelola Kasir</h1>
            <p class="text-gray-400 text-sm mt-1">Kelola akun kasir yang dapat mengakses sistem.</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-bold text-sm transition hover:opacity-90 self-start flex-shrink-0"
            style="background: linear-gradient(135deg, #0f2044, #1e4d8c); box-shadow: 0 4px 12px rgba(15,32,68,0.2)">
            ＋ Tambah Kasir
        </a>
    </div>

    <div class="anim-fade-up delay-2 bg-white rounded-2xl border border-gray-100 overflow-hidden"
        style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
        <table id="tabel-kasir" class="w-full text-sm" style="width:100%">
            <thead>
                <tr style="background: #f8faff; border-bottom: 2px solid #e2e8f0">
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Kasir</th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Email</th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Total Transaksi</th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Hari Ini</th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Bergabung</th>
                    <th class="px-5 py-4 text-left whitespace-nowrap"
                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em">
                        Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="table-row" style="border-top: 1px solid #f1f5f9">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-sm text-white flex-shrink-0"
                                    style="background: linear-gradient(135deg, #0f2044, #4a9eff)">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 whitespace-nowrap">{{ $user->name }}</p>
                                    <p class="text-xs" style="color: #4a9eff">Kasir</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-500 text-sm whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="font-black text-gray-800">{{ $user->transaksis->count() }}</span>
                            <span class="text-xs text-gray-400 ml-1">transaksi</span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="font-black text-gray-800">
                                {{ $user->transaksis->where('created_at', '>=', today())->count() }}
                            </span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-gray-400 text-xs">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-xs font-bold hover:underline"
                                    style="color: #1a3a6b">Edit</a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                    onsubmit="return confirm('Hapus kasir ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-bold hover:underline"
                                        style="color: #dc2626">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#tabel-kasir').DataTable({
                    responsive: false,
                    scrollX: true,
                    pageLength: 10,
                    language: {
                        search: "🔍",
                        searchPlaceholder: "Cari kasir...",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_–_END_ dari _TOTAL_ kasir",
                        infoEmpty: "Tidak ada data",
                        paginate: {
                            previous: "‹",
                            next: "›"
                        },
                        zeroRecords: "Tidak ada kasir yang cocok",
                        emptyTable: "Belum ada kasir"
                    },
                    dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4"f>' +
                        'rt' +
                        '<"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-t border-gray-50"ip>',
                    order: [
                        [4, 'desc']
                    ],
                    columnDefs: [{
                        orderable: false,
                        targets: [5]
                    }]
                });
            });
        </script>
    @endpush

@endsection
