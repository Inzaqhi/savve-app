@extends('layouts.kasir')
@section('title', 'Tambah Barang')

@section('content')

    <div class="anim-fade-up delay-1 flex justify-between items-start mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #7c3aed">Transaksi</p>
            <h1 class="text-2xl font-black text-gray-900">Tambah Barang</h1>
            <p class="text-gray-400 text-sm mt-1">Tambah barang baru ke transaksi yang sudah ada.</p>
        </div>
        <a href="{{ route('kasir.transaksi.show', $transaksi) }}"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition flex-shrink-0"
            style="background: white; border: 1.5px solid #e2e8f0; color: #374151"
            onmouseover="this.style.background='#faf5ff'" onmouseout="this.style.background='white'">
            ← Kembali
        </a>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Form --}}
        <div class="flex-1">
            <form action="{{ route('kasir.transaksi.simpan-barang', $transaksi) }}" method="POST" id="form-tambah">
                @csrf

                {{-- Info Transaksi --}}
                <div class="anim-fade-up delay-2 bg-white rounded-2xl border border-gray-100 p-5 mb-4"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <div class="flex flex-wrap items-start gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                style="background: linear-gradient(135deg, #1e1035, #4c1d95)">
                                <span class="text-white text-sm font-black">📋</span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Transaksi</p>
                                <p class="font-black text-gray-800 font-mono">{{ $transaksi->nomor_transaksi }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">Penitip</p>
                            <p class="font-bold text-gray-800">{{ $transaksi->nama_penitip }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">Event</p>
                            <p class="font-bold text-gray-800">{{ $transaksi->event->nama_event }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">Barang Saat Ini</p>
                            <p class="font-black text-gray-800">{{ $transaksi->details->count() }} item</p>
                        </div>
                    </div>
                </div>

                {{-- Barang yang sudah ada --}}
                <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 p-5 mb-4"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <p class="font-black text-gray-800 mb-3">Barang yang Sudah Dititipkan</p>
                    <div class="space-y-2">
                        @foreach ($transaksi->details as $d)
                            <div class="flex items-center justify-between px-4 py-3 rounded-xl"
                                style="background: #faf5ff; border: 1px solid #ede9fe">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black text-white"
                                        style="background: linear-gradient(135deg, #5b21b6, #a78bfa)">
                                        {{ $loop->iteration }}
                                    </span>
                                    <span class="font-semibold text-gray-700 text-sm">
                                        {{ $d->jenis_barang_string }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold"
                                        style="background: #ede9fe; color: #7c3aed">{{ $d->ukuran }}</span>
                                    <span class="text-xs text-gray-500">x{{ count($d->jenis_barang ?? []) }} jenis</span>
                                    <span class="font-bold text-gray-800 text-sm">
                                        Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Form Tambah Barang Baru --}}
                <div class="anim-fade-up delay-4 bg-white rounded-2xl border border-gray-100 p-6 mb-4"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <div class="flex justify-between items-center mb-4">
                        <p class="font-black text-gray-800">Tambah Barang Baru</p>
                        <button type="button" id="tambah-barang"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-white font-bold text-xs transition hover:opacity-90"
                            style="background: linear-gradient(135deg, #5b21b6, #7c3aed)">
                            + Tambah Item
                        </button>
                    </div>

                    <div id="barang-container" class="space-y-4">
                        {{-- Item pertama --}}
                        <div class="barang-item rounded-xl p-4" style="background: #faf5ff; border: 1.5px solid #ede9fe">
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                        style="color: #64748b">Kategori Barang</label>
                                    <select name="barang[0][kategori_id]"
                                        class="kategori-select w-full rounded-xl px-3 py-2.5 text-sm transition"
                                        style="background: white; border: 1.5px solid #ddd6fe; color: #374151">
                                        @foreach ($kategoris as $k)
                                            <option value="{{ $k->id }}" data-custom="{{ $k->is_custom }}">
                                                {{ $k->nama_kategori }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                        style="color: #64748b">Jumlah</label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="changeQty(this, -1)"
                                            class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-lg flex-shrink-0 transition"
                                            style="background: white; border: 1.5px solid #ddd6fe; color: #7c3aed">−</button>
                                        <input type="number" name="barang[0][jumlah]" value="1" min="1"
                                            class="flex-1 text-center rounded-xl py-2 text-sm font-bold transition"
                                            style="background: white; border: 1.5px solid #ddd6fe; color: #374151">
                                        <button type="button" onclick="changeQty(this, 1)"
                                            class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-lg flex-shrink-0 transition"
                                            style="background: white; border: 1.5px solid #ddd6fe; color: #7c3aed">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="nama-custom-wrapper mb-3" style="display:none">
                                <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                    style="color: #64748b">Nama Barang</label>
                                <input type="text" name="barang[0][nama_custom]"
                                    class="w-full rounded-xl px-3 py-2.5 text-sm transition"
                                    style="background: white; border: 1.5px solid #ddd6fe; color: #374151"
                                    placeholder="Tulis nama barang...">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider mb-2"
                                    style="color: #64748b">Ukuran</label>
                                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                                    @foreach (['S', 'M', 'L', 'XL', 'Gadget'] as $u)
                                        <label class="ukuran-label cursor-pointer"
                                            style="${u === 'Gadget' ? 'grid-column:1 / span 2;' : ''}">
                                            <input type="radio" name="barang[0][ukuran]" value="{{ $u }}"
                                                class="hidden ukuran-radio" {{ $u === 'S' ? 'checked' : '' }}>
                                            <div class="ukuran-box border-2 rounded-xl py-2.5 text-center font-bold text-sm transition"
                                                style="{{ $u === 'S' ? 'border-color: #7c3aed; color: #7c3aed; background: white;' : 'border-color: #ddd6fe; color: #94a3b8; background: white;' }}">
                                                {{ $u }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="anim-fade-up delay-5 flex gap-3">
                    <button type="submit"
                        class="flex-1 flex items-center justify-center gap-2 py-3.5 rounded-xl text-white font-bold text-sm transition hover:opacity-90"
                        style="background: linear-gradient(135deg, #5b21b6, #7c3aed); box-shadow: 0 4px 12px rgba(91,33,182,0.25)">
                        💾 Simpan Tambahan Barang
                    </button>
                    <a href="{{ route('kasir.transaksi.show', $transaksi) }}"
                        class="px-6 py-3.5 rounded-xl font-bold text-sm transition"
                        style="background: #f1f5f9; color: #64748b">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- Panel Kanan --}}
        <div class="w-full lg:w-64 flex-shrink-0">
            <div class="anim-fade-up delay-2 rounded-2xl p-5 text-white"
                style="background: linear-gradient(135deg, #1e1035, #2d1b69, #4c1d95)">
                <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color: #c4b5fd">Total Saat Ini</p>
                <p class="text-2xl font-black mb-1">
                    Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}
                </p>
                <p class="text-xs" style="color: rgba(255,255,255,0.5)">
                    {{ $transaksi->details->count() }} item · {{ $transaksi->details->sum('jumlah') }} unit
                </p>
                <div class="mt-4 pt-4" style="border-top: 1px solid rgba(255,255,255,0.1)">
                    <p class="text-xs" style="color: rgba(255,255,255,0.5)">Setelah tambah barang, total akan diperbarui
                        otomatis.</p>
                </div>
            </div>

            <div class="anim-fade-up delay-3 rounded-2xl p-5"
                style="background: linear-gradient(135deg, #1e293b, #334155)">
                <p class="font-bold text-white text-sm mb-2">💡 Info</p>
                <ul class="space-y-2 text-xs" style="color: #94a3b8">
                    <li>• Barang yang sudah ada tidak bisa dihapus dari sini.</li>
                    <li>• Tarif mengikuti setting event yang sama.</li>
                    <li>• Barang baru akan ditambahkan ke nota yang sama.</li>
                </ul>
            </div>
        </div>

    </div>

    <script>
        const kategoris = @json($kategoris);
        let index = 1;

        function changeQty(btn, delta) {
            const input = btn.parentElement.querySelector('input[type=number]');
            const val = parseInt(input.value) + delta;
            if (val >= 1) input.value = val;
        }

        function bindKategoriChange(select) {
            select.addEventListener('change', function() {
                const wrapper = this.closest('.barang-item').querySelector('.nama-custom-wrapper');
                const selected = this.options[this.selectedIndex];
                wrapper.style.display = selected.dataset.custom == '1' ? 'block' : 'none';
            });
        }

        function bindUkuranChange(container) {
            container.querySelectorAll('.ukuran-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    const parentItem = this.closest('.barang-item');
                    parentItem.querySelectorAll('.ukuran-box').forEach(box => {
                        box.style.borderColor = '#ddd6fe';
                        box.style.color = '#94a3b8';
                        box.style.background = 'white';
                    });
                    this.nextElementSibling.style.borderColor = '#7c3aed';
                    this.nextElementSibling.style.color = '#7c3aed';
                    this.nextElementSibling.style.background = '#faf5ff';
                });
            });
        }

        document.querySelectorAll('.kategori-select').forEach(bindKategoriChange);
        document.querySelectorAll('.barang-item').forEach(item => bindUkuranChange(item));

        document.getElementById('tambah-barang').addEventListener('click', function() {
            const container = document.getElementById('barang-container');
            const div = document.createElement('div');
            div.className = 'barang-item rounded-xl p-4 relative';
            div.style.cssText = 'background: #faf5ff; border: 1.5px solid #ede9fe;';
            div.innerHTML = `
            <button type="button" onclick="this.closest('.barang-item').remove()"
                class="absolute top-3 right-3 text-xs font-bold"
                style="color: #ef4444">✕ Hapus</button>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color: #64748b">Kategori Barang</label>
                    <select name="barang[${index}][kategori_id]"
                        class="kategori-select w-full rounded-xl px-3 py-2.5 text-sm"
                        style="background: white; border: 1.5px solid #ddd6fe; color: #374151">
                        ${kategoris.map(k => `<option value="${k.id}" data-custom="${k.is_custom}">${k.nama_kategori}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color: #64748b">Jumlah</label>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="changeQty(this, -1)"
                            class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-lg flex-shrink-0"
                            style="background: white; border: 1.5px solid #ddd6fe; color: #7c3aed">−</button>
                        <input type="number" name="barang[${index}][jumlah]" value="1" min="1"
                            class="flex-1 text-center rounded-xl py-2 text-sm font-bold"
                            style="background: white; border: 1.5px solid #ddd6fe; color: #374151">
                        <button type="button" onclick="changeQty(this, 1)"
                            class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-lg flex-shrink-0"
                            style="background: white; border: 1.5px solid #ddd6fe; color: #7c3aed">+</button>
                    </div>
                </div>
            </div>
            <div class="nama-custom-wrapper mb-3" style="display:none">
                <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color: #64748b">Nama Barang</label>
                <input type="text" name="barang[${index}][nama_custom]"
                    class="w-full rounded-xl px-3 py-2.5 text-sm"
                    style="background: white; border: 1.5px solid #ddd6fe; color: #374151"
                    placeholder="Tulis nama barang...">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: #64748b">Ukuran</label>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                    ${['S','M','L','XL','Gadget'].map((u, i) => `
                                                <label class="ukuran-label cursor-pointer">
                                                    <input type="radio" name="barang[${index}][ukuran]" value="${u}" class="hidden ukuran-radio" ${i===0?'checked':''}>
                                                    <div class="ukuran-box border-2 rounded-xl py-2.5 text-center font-bold text-sm transition"
                                                        style="${i===0 ? 'border-color: #7c3aed; color: #7c3aed; background: white;' : 'border-color: #ddd6fe; color: #94a3b8; background: white;'}">
                                                        ${u}
                                                    </div>
                                                </label>`).join('')}
                </div>
            </div>
        `;
            container.appendChild(div);
            bindKategoriChange(div.querySelector('.kategori-select'));
            bindUkuranChange(div);
            index++;
        });
    </script>

@endsection
