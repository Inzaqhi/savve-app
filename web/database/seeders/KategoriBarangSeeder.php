<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriBarang;

class KategoriBarangSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = ['Tas', 'Ransel', 'Sepatu', 'Helm', 'Jaket', 'Kamera'];

        foreach ($kategoris as $nama) {
            KategoriBarang::create([
                'nama_kategori' => $nama,
                'is_custom'     => false,
            ]);
        }

        // Kategori "Lainnya" sebagai opsi custom
        KategoriBarang::create([
            'nama_kategori' => 'Lainnya',
            'is_custom'     => true,
        ]);
    }
}
