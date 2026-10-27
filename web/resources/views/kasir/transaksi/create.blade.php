@extends('layouts.kasir')
@section('title', 'Transaksi Penitipan Baru')

@section('content')

    {{-- Header --}}
    <div class="anim-fade-up delay-1 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #7c3aed">Penitipan</p>
            <h1 class="text-xl lg:text-2xl font-black text-gray-900">Transaksi Penitipan Baru</h1>
            <p class="text-gray-400 text-sm mt-1">
                Event: <span class="font-bold" style="color: #7c3aed">{{ $event->nama_event }}</span>
                <span class="font-mono text-xs ml-1" style="color: #a78bfa">({{ $event->kode_event }})</span>
            </p>
        </div>
        <div class="flex items-center gap-2 self-start flex-shrink-0">
            <span class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full"
                style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse inline-block"></span>
                LIVE
            </span>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        {{-- ═══ FORM ═══ --}}
        <div class="flex-1 min-w-0">

            {{-- Nomor Preview Mobile (hanya tampil di HP) --}}
            <div class="lg:hidden rounded-2xl px-5 py-4 mb-4 text-white relative overflow-hidden"
                style="background: linear-gradient(135deg, #1e1035, #4c1d95)">
                <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color: #c4b5fd">
                    Nomor Transaksi
                </p>
                <p id="nomor-preview-mobile" class="text-xl font-black tracking-tight font-mono">
                    SVV-{{ $event->kode_event }}-????
                </p>
                <p class="text-xs mt-1" style="color: rgba(255,255,255,0.5)">
                    Event: {{ $event->nama_event }}
                </p>
                <div class="absolute -bottom-4 -right-4 w-20 h-20 rounded-full" style="background: rgba(255,255,255,0.05)">
                </div>
            </div>

            <form action="{{ route('kasir.transaksi.store') }}" method="POST" id="form-transaksi">
                @csrf

                {{-- Data Customer --}}
                <div class="anim-fade-up delay-2 bg-white rounded-2xl border border-gray-100 p-5 lg:p-6 mb-4"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <h3 class="font-black text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-white text-xs flex-shrink-0"
                            style="background: linear-gradient(135deg, #5b21b6, #7c3aed)">1</span>
                        Data Customer
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                style="color: #64748b">Nama Customer</label>
                            <input type="text" name="nama_penitip" value="{{ old('nama_penitip') }}"
                                class="w-full rounded-xl px-4 py-3 text-sm transition"
                                style="background: #faf5ff; border: 1.5px solid #ede9fe; color: #374151"
                                placeholder="Nama lengkap penitip"
                                onfocus="this.style.borderColor='#a78bfa'; this.style.boxShadow='0 0 0 3px rgba(167,139,250,0.1)'"
                                onblur="this.style.borderColor='#ede9fe'; this.style.boxShadow='none'">
                            @error('nama_penitip')
                                <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5"
                                style="color: #64748b">Nomor HP / WhatsApp</label>
                            <input type="text" name="no_whatsapp" value="{{ old('no_whatsapp') }}"
                                class="w-full rounded-xl px-4 py-3 text-sm transition"
                                style="background: #faf5ff; border: 1.5px solid #ede9fe; color: #374151"
                                placeholder="08xxxxxxxxxx"
                                onfocus="this.style.borderColor='#a78bfa'; this.style.boxShadow='0 0 0 3px rgba(167,139,250,0.1)'"
                                onblur="this.style.borderColor='#ede9fe'; this.style.boxShadow='none'">
                            @error('no_whatsapp')
                                <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Daftar Barang --}}
                <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 p-5 lg:p-6 mb-4"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-black text-gray-800 flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-lg flex items-center justify-center text-white text-xs flex-shrink-0"
                                style="background: linear-gradient(135deg, #5b21b6, #7c3aed)">2</span>
                            Barang yang Dititipkan
                        </h3>
                        <button type="button" id="btn-tambah-item"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-white font-bold text-xs transition hover:opacity-90"
                            style="background: linear-gradient(135deg, #5b21b6, #7c3aed)">
                            + Tambah Kategori
                        </button>
                    </div>

                    <div id="items-container" class="space-y-4">
                        {{-- Item pertama --}}
                        <div class="item-barang rounded-2xl p-4 relative"
                            style="background: #faf5ff; border: 1.5px solid #ede9fe">
                            @include('kasir.transaksi._item_barang', [
                                'index' => 0,
                                'jenisBarangs' => $jenisBarangs,
                                'tarifs' => $tarifs,
                                'isFirst' => true,
                            ])
                        </div>
                    </div>

                    {{-- Metode Bayar --}}
                    <div class="mt-6">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: #64748b">Metode
                            Pembayaran</label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach (['QRIS' => '📱', 'Cash' => '💵', 'Web' => '🌐'] as $metode => $icon)
                                <label class="metode-label cursor-pointer">
                                    <input type="radio" name="metode_bayar" value="{{ $metode }}"
                                        class="hidden metode-radio"
                                        {{ old('metode_bayar', 'Cash') === $metode ? 'checked' : '' }}>
                                    <div class="metode-box border-2 rounded-xl py-3 text-center transition"
                                        style="{{ old('metode_bayar', 'Cash') === $metode
                                            ? 'border-color: #7c3aed; background: #faf5ff;'
                                            : 'border-color: #ede9fe; background: white;' }}">
                                        <div class="text-xl mb-1">{{ $icon }}</div>
                                        <p class="text-xs font-bold"
                                            style="{{ old('metode_bayar', 'Cash') === $metode ? 'color: #7c3aed' : 'color: #94a3b8' }}">
                                            {{ $metode }}
                                        </p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('metode_bayar')
                            <p class="text-red-500 text-xs mt-1">⚠ {{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Foto Barang --}}
                <div class="anim-fade-up delay-4 bg-white rounded-2xl border border-gray-100 p-5 lg:p-6 mb-4"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                    <h3 class="font-black text-gray-800 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-white text-xs flex-shrink-0"
                            style="background: linear-gradient(135deg, #5b21b6, #7c3aed)">3</span>
                        Foto Barang
                        <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span>
                    </h3>

                    <input type="hidden" name="foto_penitipan" id="foto_penitipan_input">

                    <div id="foto-preview-wrapper" class="hidden mb-3">
                        <div class="relative rounded-xl overflow-hidden" style="border: 1.5px solid #ddd6fe">
                            <img id="foto-preview" src="" alt="Preview" class="w-full max-h-52 object-cover">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent pointer-events-none">
                            </div>
                            <button type="button" onclick="hapusFoto()"
                                class="absolute top-2 right-2 w-8 h-8 flex items-center justify-center rounded-full text-white font-bold"
                                style="background: rgba(0,0,0,0.6)">✕</button>
                            <div class="absolute bottom-2 left-2">
                                <span class="px-2 py-1 rounded-lg text-xs font-bold text-white"
                                    style="background: rgba(91,33,182,0.8)">✅ Foto tersimpan</span>
                            </div>
                        </div>
                        <button type="button" onclick="hapusFoto()"
                            class="mt-2 text-xs font-semibold flex items-center gap-1" style="color: #dc2626">
                            🗑️ Hapus foto
                        </button>
                    </div>

                    <div id="foto-buttons" class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="bukaKamera('penitipan')"
                            class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm transition hover:opacity-90"
                            style="background: linear-gradient(135deg, #faf5ff, #f3e8ff); color: #7c3aed; border: 1.5px solid #e9d5ff">
                            <span>📷</span> Ambil Foto
                        </button>
                        <label
                            class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm cursor-pointer transition hover:opacity-90"
                            style="background: linear-gradient(135deg, #f8fbff, #eef4ff); color: #1d4ed8; border: 1.5px solid #dbeafe">
                            <span>🖼️</span> Pilih dari Galeri
                            <input type="file" accept="image/*" class="hidden" onchange="pilihDariGaleri(this)">
                        </label>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="anim-fade-up delay-5 flex gap-3">
                    <button type="submit"
                        class="flex-1 flex items-center justify-center gap-2 py-3.5 rounded-xl text-white font-bold text-sm transition hover:opacity-90"
                        style="background: linear-gradient(135deg, #5b21b6, #7c3aed); box-shadow: 0 4px 12px rgba(91,33,182,0.25)">
                        💾 Simpan
                    </button>
                    <a href="{{ route('kasir.dashboard') }}" id="btn-batal"
                        class="text-sm flex items-center gap-2 font-semibold px-4 py-2 rounded-xl transition"
                        style="background: #fff5f5; color: #ef4444; border: 1.5px solid #fecaca">
                        ✕ Batal & Kembali
                    </a>
                </div>
            </form>
        </div>

        {{-- ═══ PANEL KANAN ═══ --}}
        <div class="hidden lg:flex w-72 flex-shrink-0 flex-col space-y-4 anim-fade-up delay-2">

            {{-- Nomor Preview --}}
            <div class="rounded-2xl p-6 text-white relative overflow-hidden"
                style="background: linear-gradient(135deg, #1e1035, #4c1d95)">
                <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color: #c4b5fd">
                    Nomor Transaksi
                </p>
                <p id="nomor-preview" class="text-2xl font-black tracking-tight leading-tight mb-2 font-mono">
                    SVV-{{ $event->kode_event }}-????
                </p>
                <p class="text-xs" style="color: rgba(255,255,255,0.5)">
                    Event: {{ $event->nama_event }}
                </p>
                <div class="absolute -bottom-4 -right-4 w-24 h-24 rounded-full"
                    style="background: rgba(255,255,255,0.05)"></div>
            </div>

            {{-- Tarif --}}
            <div class="anim-fade-up delay-3 bg-white rounded-2xl border border-gray-100 p-5"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color: #94a3b8">
                    Tarif Event Ini
                </p>
                <div class="space-y-2">
                    @foreach (['S', 'M', 'L', 'XL', 'Gadget'] as $u)
                        <div class="flex justify-between items-center">
                            <span
                                class="min-w-8 h-8 px-2 flex items-center justify-center rounded-full text-xs font-black text-white"
                                style="background: linear-gradient(135deg, #5b21b6, #7c3aed)"> {{ $u }} </span>
                            <span class="font-bold text-gray-700 text-sm">
                                Rp {{ number_format($tarifs[$u]->harga ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Summary Barang --}}
            <div class="anim-fade-up delay-4 bg-white rounded-2xl border border-gray-100 p-5"
                style="box-shadow: 0 2px 12px rgba(0,0,0,0.04)">
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color: #94a3b8">
                    Summary
                </p>
                <div id="summary-container" class="space-y-2">
                    <p class="text-xs text-gray-400 text-center py-2">Belum ada barang dipilih</p>
                </div>
                <div style="border-top: 1px solid #f5f3ff" class="mt-3 pt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-500">Total</span>
                        <span id="total-harga" class="font-black text-lg" style="color: #5b21b6">
                            Rp 0
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tips --}}
            <div class="anim-fade-up delay-5 rounded-2xl p-5"
                style="background: linear-gradient(135deg, #1e293b, #334155)">
                <p class="font-bold text-white text-sm mb-2">💡 Tips</p>
                <ul class="space-y-1.5 text-xs" style="color: #94a3b8">
                    <li>• Satu transaksi bisa berisi banyak kategori barang</li>
                    <li>• Centang semua jenis barang yang dititipkan</li>
                    <li>• Tarif dihitung per kategori (S/M/L/XL/Gadget)</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Modal Kamera Penitipan --}}
    <div id="modal-kamera-penitipan" class="hidden fixed inset-0 z-50"
        style="background: rgba(0,0,0,0.85); display: none; align-items: center; justify-content: center;">

        <div
            style="background: white; border-radius: 16px; overflow: hidden; width: fit-content; max-width: 90vw; margin: 0 auto; position: relative;">

            {{-- Header --}}
            <div class="flex justify-between items-center px-5 py-4"
                style="background: linear-gradient(135deg, #1e1035, #2d1b69)">
                <div>
                    <p class="font-black text-white text-sm">📷 Foto Barang</p>
                    <p class="text-xs mt-0.5" style="color: #c4b5fd">Pastikan barang terlihat jelas</p>
                </div>
                <button onclick="tutupKamera('penitipan')"
                    class="w-8 h-8 flex items-center justify-center rounded-xl text-white font-bold"
                    style="background: rgba(255,255,255,0.15)">✕</button>
            </div>

            {{-- Video --}}
            <div style="position: relative; background: #0f0f1a;">
                <video id="video-penitipan" autoplay playsinline
                    style="display: block; max-height: 65vh; width: auto; max-width: 90vw; transform: scaleX(-1);"></video>

                {{-- Viewfinder overlay --}}
                <div
                    style="position: absolute; inset: 0; pointer-events: none; display: flex; align-items: center; justify-content: center;">
                    <div style="width: 75%; height: 75%; position: relative;">
                        <div
                            style="position:absolute;top:0;left:0;width:24px;height:24px;border-top:2.5px solid #a78bfa;border-left:2.5px solid #a78bfa;border-radius:4px 0 0 0">
                        </div>
                        <div
                            style="position:absolute;top:0;right:0;width:24px;height:24px;border-top:2.5px solid #a78bfa;border-right:2.5px solid #a78bfa;border-radius:0 4px 0 0">
                        </div>
                        <div
                            style="position:absolute;bottom:0;left:0;width:24px;height:24px;border-bottom:2.5px solid #a78bfa;border-left:2.5px solid #a78bfa;border-radius:0 0 0 4px">
                        </div>
                        <div
                            style="position:absolute;bottom:0;right:0;width:24px;height:24px;border-bottom:2.5px solid #a78bfa;border-right:2.5px solid #a78bfa;border-radius:0 0 4px 0">
                        </div>
                    </div>
                </div>
            </div>

            <canvas id="canvas-penitipan" class="hidden"></canvas>

            {{-- Tombol --}}
            <button type="button" onclick="jepretFoto('penitipan')"
                class="w-full py-4 rounded-2xl text-white font-black text-sm flex items-center justify-center gap-2"
                style="background: linear-gradient(135deg, #5b21b6, #7c3aed)">
                📸 Ambil Foto Sekarang
            </button>
        </div>
    </div>
    </div>

    <script>
        // ── Data tarif dari server ──
        const tarifData = @json($tarifs->map(fn($t) => $t->harga));
        const jenisBarangData = @json($jenisBarangs);
        let itemIndex = 1;

        // ── Metode bayar styling ──
        document.querySelectorAll('.metode-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.metode-box').forEach(box => {
                    box.style.borderColor = '#ede9fe';
                    box.style.background = 'white';
                    box.querySelector('p').style.color = '#94a3b8';
                });
                const box = this.nextElementSibling;
                box.style.borderColor = '#7c3aed';
                box.style.background = '#faf5ff';
                box.querySelector('p').style.color = '#7c3aed';
            });
        });

        // ── Tambah Item ──
        document.getElementById('btn-tambah-item').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const div = document.createElement('div');
            div.className = 'item-barang rounded-2xl p-4 relative';
            div.style.cssText = 'background: #faf5ff; border: 1.5px solid #ede9fe;';
            div.innerHTML = buildItemHTML(itemIndex);
            container.appendChild(div);
            bindItemEvents(div, itemIndex);
            itemIndex++;
            updateSummary();
        });

        function buildItemHTML(idx) {
            const ukurans = ['S', 'M', 'L', 'XL', 'Gadget'];

            const ukuranButtons = ukurans.map((u, i) => `
<label class="ukuran-label-${idx} cursor-pointer ${u === 'Gadget' ? 'col-span-2' : ''}">
    <input type="radio" name="items[${idx}][ukuran]" value="${u}"
        class="hidden ukuran-radio" ${i === 0 ? 'checked' : ''}>
    <div class="ukuran-box border-2 rounded-xl py-2.5 text-center font-bold text-sm transition"
        style="${i === 0
            ? 'border-color:#7c3aed;color:#7c3aed;background:white;'
            : 'border-color:#ddd6fe;color:#94a3b8;background:white;'}">
        ${u}
        <div class="text-xs font-normal mt-0.5"
            style="color:${i===0?'#a78bfa':'#cbd5e1'}">
            Rp ${(tarifData[u] || 0).toLocaleString('id-ID')}
        </div>
    </div>
</label>`).join('');

            // Default tampilkan jenis barang ukuran S
            const firstUkuran = 'S';
            const jenisRows = buildJenisBarangCheckboxes(idx, firstUkuran);

            return `
    <button type="button" onclick="hapusItem(this)"
        class="absolute top-3 right-3 w-7 h-7 flex items-center justify-center rounded-full text-white text-xs font-bold"
        style="background: rgba(220,38,38,0.8)">✕</button>

    <div class="mb-3">
        <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color:#64748b">
            Kategori (Ukuran)
        </label>
        <div class="grid grid-cols-2 gap-2">
${ukuranButtons}
</div>
    </div>

    <div class="jenis-barang-wrapper">
        <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color:#64748b">
            Jenis Barang
            <span class="font-normal normal-case ml-1" style="color:#94a3b8">(pilih satu atau lebih)</span>
        </label>
        <div class="jenis-container grid grid-cols-2 gap-2">
            ${jenisRows}
        </div>
        <div class="lainnya-wrapper hidden mt-2">
            <input type="text" name="items[${idx}][jenis_barang_lainnya]"
                class="lainnya-input w-full rounded-xl px-3 py-2.5 text-sm transition"
                style="background:white;border:1.5px solid #ddd6fe;color:#374151"
                placeholder="Tulis nama barang, pisahkan dengan koma jika lebih dari satu">
        </div>
    </div>`;
        }

        function buildJenisBarangCheckboxes(idx, ukuran) {
            const items = (jenisBarangData[ukuran] || [])
                .filter(item => item.nama.toLowerCase() !== 'lainnya');

            const rows = items.map((item, i) => `
        <div class="jenis-row">
            <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl cursor-pointer transition jenis-item"
                style="background:white;border:1.5px solid #ddd6fe">
                <input type="checkbox" name="items[${idx}][barang][${i}][selected]" value="1"
                    class="jenis-checkbox"
                    style="accent-color:#7c3aed;width:14px;height:14px;flex-shrink:0">
                <span class="text-xs font-semibold text-gray-700">${item.nama}</span>
            </label>
            <input type="hidden" name="items[${idx}][barang][${i}][nama]" value="${item.nama}">
            <div class="jenis-detail-wrapper hidden mt-1.5 space-y-1.5">
                <input type="text" name="items[${idx}][barang][${i}][keterangan]"
                    class="w-full rounded-lg px-2.5 py-2 text-xs"
                    style="border:1px solid #e9d5ff;background:#faf5ff"
                    placeholder="Keterangan (opsional, mis: warna/merk)">
                <input type="text" name="items[${idx}][barang][${i}][nomor_label]"
                    class="w-full rounded-lg px-2.5 py-2 text-xs"
                    style="border:1px solid #e9d5ff;background:#faf5ff"
                    placeholder="No. Label (opsional)">
            </div>
        </div>`).join('');

            const lainnyaCheckbox = `
        <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl cursor-pointer transition jenis-item-lainnya col-span-2"
            style="background:white;border:1.5px solid #ddd6fe">
            <input type="checkbox" class="jenis-checkbox-lainnya"
                style="accent-color:#7c3aed;width:14px;height:14px;flex-shrink:0">
            <span class="text-xs font-semibold text-gray-700">Lainnya (tulis manual)</span>
        </label>`;

            return rows + lainnyaCheckbox;
        }

        function bindItemEvents(container, idx) {
            // Ukuran radio change
            container.querySelectorAll('.ukuran-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    const parentItem = this.closest('.item-barang');

                    parentItem.querySelectorAll('.ukuran-box').forEach(box => {
                        box.style.borderColor = '#ddd6fe';
                        box.style.color = '#94a3b8';
                        box.style.background = 'white';
                        box.querySelector('div').style.color = '#cbd5e1';
                    });

                    const box = this.nextElementSibling;
                    box.style.borderColor = '#7c3aed';
                    box.style.color = '#7c3aed';
                    box.style.background = 'white';
                    box.querySelector('div').style.color = '#a78bfa';

                    const ukuran = this.value;
                    const jenisContainer = parentItem.querySelector('.jenis-container');
                    jenisContainer.innerHTML = buildJenisBarangCheckboxes(idx, ukuran);
                    bindCheckboxEvents(jenisContainer);

                    // reset input manual saat ukuran berubah
                    const lainnyaWrapper = parentItem.querySelector('.lainnya-wrapper');
                    lainnyaWrapper.classList.add('hidden');
                    lainnyaWrapper.querySelector('.lainnya-input').value = '';

                    updateSummary();
                });
            });

            // Bind checkbox events
            bindCheckboxEvents(container);
        }

        function bindCheckboxEvents(container) {
            container.querySelectorAll('.jenis-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    const row = this.closest('.jenis-row');
                    const label = this.closest('label');
                    const detailWrapper = row.querySelector('.jenis-detail-wrapper');
                    if (this.checked) {
                        label.style.borderColor = '#7c3aed';
                        label.style.background = '#faf5ff';
                        detailWrapper.classList.remove('hidden');
                    } else {
                        label.style.borderColor = '#ddd6fe';
                        label.style.background = 'white';
                        detailWrapper.classList.add('hidden');
                        detailWrapper.querySelectorAll('input').forEach(inp => inp.value = '');
                    }
                    updateSummary();
                });
            });

            container.querySelectorAll('.jenis-checkbox-lainnya').forEach(cb => {
                cb.addEventListener('change', function() {
                    const label = this.closest('label');
                    const wrapper = this.closest('.jenis-barang-wrapper').querySelector('.lainnya-wrapper');
                    if (this.checked) {
                        label.style.borderColor = '#7c3aed';
                        label.style.background = '#faf5ff';
                        wrapper.classList.remove('hidden');
                        wrapper.querySelector('.lainnya-input').focus();
                    } else {
                        label.style.borderColor = '#ddd6fe';
                        label.style.background = 'white';
                        wrapper.classList.add('hidden');
                        wrapper.querySelector('.lainnya-input').value = '';
                    }
                    updateSummary();
                });
            });

            container.querySelectorAll('.lainnya-input').forEach(inp => {
                inp.addEventListener('input', updateSummary);
            });
        }

        function hapusItem(btn) {
            const item = btn.closest('.item-barang');
            const container = document.getElementById('items-container');
            if (container.querySelectorAll('.item-barang').length <= 1) {
                alert('Minimal harus ada 1 kategori barang.');
                return;
            }
            item.remove();
            updateSummary();
        }

        // ── Summary ──
        function updateSummary() {
            const items = document.querySelectorAll('.item-barang');
            let html = '';
            let total = 0;

            items.forEach(item => {
                const ukuranChecked = item.querySelector('.ukuran-radio:checked');
                if (!ukuranChecked) return;

                const ukuran = ukuranChecked.value;
                const harga = tarifData[ukuran] || 0;

                let namaList = [...item.querySelectorAll('.jenis-checkbox:checked')].map(cb => {
                    const row = cb.closest('.jenis-row');
                    return row.querySelector('input[type="hidden"]').value;
                });

                const lainnyaCb = item.querySelector('.jenis-checkbox-lainnya');
                const lainnyaInput = item.querySelector('.lainnya-input');
                if (lainnyaCb && lainnyaCb.checked && lainnyaInput.value.trim() !== '') {
                    const extras = lainnyaInput.value.split(',').map(s => s.trim()).filter(Boolean);
                    namaList = namaList.concat(extras);
                }

                if (namaList.length > 0) {
                    const namaBarang = namaList.join(', ');
                    html += `
            <div class="flex justify-between items-start gap-2">
                <div>
                    <span class="px-1.5 py-0.5 rounded text-xs font-black text-white"
                        style="background:#5b21b6">${ukuran}</span>
                    <p class="text-xs text-gray-500 mt-0.5">${namaBarang}</p>
                </div>
                <span class="text-xs font-bold text-gray-700 flex-shrink-0">
                    Rp ${harga.toLocaleString('id-ID')}
                </span>
            </div>`;
                    total += harga;
                }
            });

            document.getElementById('summary-container').innerHTML =
                html || '<p class="text-xs text-gray-400 text-center py-2">Belum ada barang dipilih</p>';
            document.getElementById('total-harga').textContent =
                'Rp ' + total.toLocaleString('id-ID');
        }

        // ── Bind events pada item pertama ──
        document.querySelectorAll('.item-barang').forEach((item, idx) => {
            bindItemEvents(item, idx);
        });
        updateSummary();

        // ── Kamera ──
        let streamPenitipan = null;

        async function bukaKamera(type) {
            const modal = document.getElementById('modal-kamera-' + type);
            modal.style.display = 'flex'; // ← ganti dari classList.remove('hidden')
            try {
                streamPenitipan = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: {
                            ideal: 'environment'
                        },
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        }
                    }
                });
            } catch (e) {
                streamPenitipan = await navigator.mediaDevices.getUserMedia({
                    video: true
                });
            }
            const video = document.getElementById('video-' + type);
            video.srcObject = streamPenitipan;
        }

        function tutupKamera(type) {
            streamPenitipan?.getTracks().forEach(t => t.stop());
            streamPenitipan = null;
            document.getElementById('modal-kamera-' + type).style.display = 'none';
        }

        function tutupKamera(type) {
            if (streamPenitipan) {
                streamPenitipan.getTracks().forEach(function(track) {
                    track.stop();
                });
                streamPenitipan = null;
            }
            const modal = document.getElementById('modal-kamera-' + type);
            modal.style.display = 'none';
            modal.classList.add('hidden'); // ← double kill, pastikan tertutup
        }

        function jepretFoto(type) {
            const video = document.getElementById('video-' + type);
            const canvas = document.getElementById('canvas-' + type);
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);

            document.getElementById('foto_penitipan_input').value = dataUrl;
            document.getElementById('foto-preview').src = dataUrl;
            document.getElementById('foto-preview-wrapper').classList.remove('hidden');
            document.getElementById('foto-buttons').classList.add('hidden');
            tutupKamera(type);
        }

        function pilihDariGaleri(input) {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('foto_penitipan_input').value = e.target.result;
                document.getElementById('foto-preview').src = e.target.result;
                document.getElementById('foto-preview-wrapper').classList.remove('hidden');
                document.getElementById('foto-buttons').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        function hapusFoto() {
            document.getElementById('foto_penitipan_input').value = '';
            document.getElementById('foto-preview-wrapper').classList.add('hidden');
            document.getElementById('foto-buttons').classList.remove('hidden');
        }

        // ── Generate nomor preview ──
        async function generatePreviewNomor() {
            try {
                const res = await fetch('{{ route('kasir.transaksi.count-today') }}');
                const data = await res.json();
                const urutan = String(data.count + 1).padStart(4, '0');
                const nomor = `SVV-{{ $event->kode_event }}-${urutan}`;
                document.getElementById('nomor-preview').textContent = nomor;
                // ← tambah ini
                const mobileEl = document.getElementById('nomor-preview-mobile');
                if (mobileEl) mobileEl.textContent = nomor;
            } catch (e) {}
        }
        generatePreviewNomor();

        // ── Konfirmasi keluar ──
        let formDirty = false;
        document.getElementById('form-transaksi').addEventListener('input', () => formDirty = true);
        document.getElementById('form-transaksi').addEventListener('change', () => formDirty = true);
        document.getElementById('form-transaksi').addEventListener('submit', () => formDirty = false);
        window.addEventListener('beforeunload', e => {
            if (formDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>

@endsection
