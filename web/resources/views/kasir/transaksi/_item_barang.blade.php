@php
    $detail = $detail ?? null;
    $selectedUkuran = $detail->ukuran ?? 'S';
    $selectedJenisFormatted = $detail ? $detail->jenisBarangFormatted() : [];
    $jenisBarangNamaAktif = collect($jenisBarangs[$selectedUkuran] ?? [])
        ->pluck('nama')
        ->map(fn($n) => strtolower($n))
        ->all();
    $checkedNama = collect($selectedJenisFormatted)
        ->filter(fn($jb) => in_array(strtolower($jb['nama']), $jenisBarangNamaAktif))
        ->keyBy(fn($jb) => strtolower($jb['nama']));
    $lainnyaItems = collect($selectedJenisFormatted)->reject(
        fn($jb) => in_array(strtolower($jb['nama']), $jenisBarangNamaAktif),
    );
    $lainnyaText = $lainnyaItems->pluck('nama')->implode(', ');
@endphp

{{-- Kategori (Ukuran) --}}
<div class="mb-3">
    <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: #64748b">
        Kategori (Ukuran)
    </label>
    <div class="grid grid-cols-2 gap-2">
        @foreach (['S', 'M', 'L', 'XL', 'Gadget'] as $u)
            <label class="cursor-pointer" style="{{ $u == 'Gadget' ? 'grid-column:1 / span 2;' : '' }}">
                <input type="radio" name="items[{{ $index }}][ukuran]" value="{{ $u }}"
                    class="hidden ukuran-radio" {{ $u === $selectedUkuran ? 'checked' : '' }}>
                <div class="ukuran-box border-2 rounded-xl py-2.5 text-center font-bold text-sm transition"
                    style="{{ $u === $selectedUkuran
                        ? 'border-color:#7c3aed;color:#7c3aed;background:white;'
                        : 'border-color:#ddd6fe;color:#94a3b8;background:white;' }}">
                    {{ $u }}
                    <div class="text-xs font-normal mt-0.5"
                        style="color: {{ $u === $selectedUkuran ? '#a78bfa' : '#cbd5e1' }}">
                        Rp {{ number_format($tarifs[$u]->harga ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </label>
        @endforeach
    </div>
</div>

{{-- Jenis Barang --}}
<div class="jenis-barang-wrapper">
    <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: #64748b">
        Jenis Barang
        <span class="font-normal normal-case ml-1" style="color: #94a3b8">(pilih satu atau lebih)</span>
    </label>
    @error("items.{$index}.jenis_barang")
        <p class="text-red-500 text-xs mt-1 error-jenis-barang">⚠ {{ $message }}</p>
    @enderror
    <div class="jenis-container grid grid-cols-2 gap-2">
        @foreach ($jenisBarangs[$selectedUkuran] ?? [] as $jenis)
            @continue(strtolower($jenis['nama']) === 'lainnya')
            @php
                $matched = $checkedNama->get(strtolower($jenis['nama']));
                $isChecked = $matched !== null;
            @endphp
            <div class="jenis-row">
                <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl cursor-pointer transition jenis-item"
                    style="background: {{ $isChecked ? '#faf5ff' : 'white' }}; border: 1.5px solid {{ $isChecked ? '#7c3aed' : '#ddd6fe' }}">
                    <input type="checkbox" name="items[{{ $index }}][barang][{{ $loop->index }}][selected]"
                        value="1" class="jenis-checkbox" {{ $isChecked ? 'checked' : '' }}
                        style="accent-color: #7c3aed; width: 14px; height: 14px; flex-shrink: 0">
                    <span class="text-xs font-semibold text-gray-700">{{ $jenis['nama'] }}</span>
                </label>
                <input type="hidden" name="items[{{ $index }}][barang][{{ $loop->index }}][nama]"
                    value="{{ $jenis['nama'] }}">
                <div class="jenis-detail-wrapper {{ $isChecked ? '' : 'hidden' }} mt-1.5 space-y-1.5">
                    <input type="text" name="items[{{ $index }}][barang][{{ $loop->index }}][keterangan]"
                        value="{{ $matched['keterangan'] ?? '' }}" class="w-full rounded-lg px-2.5 py-2 text-xs"
                        style="border:1px solid #e9d5ff;background:#faf5ff"
                        placeholder="Keterangan (opsional, mis: warna/merk)">
                    <input type="text" name="items[{{ $index }}][barang][{{ $loop->index }}][nomor_label]"
                        value="{{ $matched['nomor_label'] ?? '' }}" class="w-full rounded-lg px-2.5 py-2 text-xs"
                        style="border:1px solid #e9d5ff;background:#faf5ff" placeholder="No. Label (opsional)">
                </div>
            </div>
        @endforeach

        {{-- Toggle untuk input manual --}}
        <label
            class="flex items-center gap-2 px-3 py-2.5 rounded-xl cursor-pointer transition jenis-item-lainnya col-span-2"
            style="background: {{ $lainnyaText ? '#faf5ff' : 'white' }}; border: 1.5px solid {{ $lainnyaText ? '#7c3aed' : '#ddd6fe' }}">
            <input type="checkbox" class="jenis-checkbox-lainnya" {{ $lainnyaText ? 'checked' : '' }}
                style="accent-color: #7c3aed; width: 14px; height: 14px; flex-shrink: 0">
            <span class="text-xs font-semibold text-gray-700">Lainnya (tulis manual)</span>
        </label>
    </div>

    {{-- Input manual, muncul kalau "Lainnya" dicentang --}}
    <div class="lainnya-wrapper {{ $lainnyaText ? '' : 'hidden' }} mt-2">
        <input type="text" name="items[{{ $index }}][jenis_barang_lainnya]" value="{{ $lainnyaText }}"
            class="lainnya-input w-full rounded-xl px-3 py-2.5 text-sm transition"
            style="background: white; border: 1.5px solid #ddd6fe; color: #374151"
            placeholder="Tulis nama barang, pisahkan dengan koma jika lebih dari satu">
    </div>
</div>
