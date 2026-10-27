<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $fillable = [
        'transaksi_id',
        'ukuran',
        'jenis_barang',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'jenis_barang' => 'array',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_id');
    }

    // Helper: menampilkan jenis barang sebagai string
    public function getJenisBarangStringAttribute(): string
    {
        return collect($this->jenisBarangFormatted())->pluck('nama')->implode(', ');
    }

    // Helper: menghitung jumlah jenis barang
    public function getJumlahJenisAttribute(): int
    {
        return count($this->jenis_barang ?? []);
    }

    public function jenisBarangFormatted(): array
    {
        return collect($this->jenis_barang ?? [])->map(function ($item) {
            if (is_array($item)) {
                return [
                    'nama'        => $item['nama'] ?? '-',
                    'keterangan'  => $item['keterangan'] ?? null,
                    'nomor_label' => $item['nomor_label'] ?? null,
                ];
            }
            // Data lama: cuma string nama barang
            return ['nama' => $item, 'keterangan' => null, 'nomor_label' => null];
        })->all();
    }
    public function getJenisBarangLabelAttribute(): string
    {
        return collect($this->jenisBarangFormatted())->map(function ($it) {
            $extra = array_filter([
                $it['keterangan'] ?: null,
                $it['nomor_label'] ? "#{$it['nomor_label']}" : null,
            ]);
            return $it['nama'] . ($extra ? ' (' . implode(', ', $extra) . ')' : '');
        })->implode(', ');
    }
}
