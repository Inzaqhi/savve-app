<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisBarang;

class JenisBarangSeeder extends Seeder
{
    public function run(): void
    {
        JenisBarang::truncate();

        $data = [
            // Ukuran S
            ['ukuran' => 'S', 'nama' => 'Bedak',       'urutan' => 1],
            ['ukuran' => 'S', 'nama' => 'Cermin',  'urutan' => 2],
            ['ukuran' => 'S', 'nama' => 'Lipstik',     'urutan' => 3],
            ['ukuran' => 'S', 'nama' => 'Parfum',   'urutan' => 4],
            ['ukuran' => 'S', 'nama' => 'Rokok',     'urutan' => 5],
            ['ukuran' => 'S', 'nama' => 'Tiket',     'urutan' => 6],
            ['ukuran' => 'S', 'nama' => 'Vape',     'urutan' => 7],
            ['ukuran' => 'S', 'nama' => 'Lainnya',      'urutan' => 99],

            // Ukuran M
            ['ukuran' => 'M', 'nama' => 'Bedak',       'urutan' => 1],
            ['ukuran' => 'M', 'nama' => 'Make-up',         'urutan' => 2],
            ['ukuran' => 'M', 'nama' => 'Parfum',       'urutan' => 3],
            ['ukuran' => 'M', 'nama' => 'Payung',    'urutan' => 4],
            ['ukuran' => 'M', 'nama' => 'Tripod',    'urutan' => 5],
            ['ukuran' => 'M', 'nama' => 'Tumbler',    'urutan' => 6],
            ['ukuran' => 'M', 'nama' => 'Vape',    'urutan' => 7],
            ['ukuran' => 'M', 'nama' => 'Lainnya',      'urutan' => 99],

            // Ukuran L
            ['ukuran' => 'L', 'nama' => 'Bunga',        'urutan' => 1],
            ['ukuran' => 'L', 'nama' => 'Helm',        'urutan' => 2],
            ['ukuran' => 'L', 'nama' => 'Jaket',         'urutan' => 3],
            ['ukuran' => 'L', 'nama' => 'Slingbag',      'urutan' => 4],
            ['ukuran' => 'L', 'nama' => 'Totebag',       'urutan' => 5],
            ['ukuran' => 'L', 'nama' => 'Lainnya',      'urutan' => 99],

            // Ukuran XL
            ['ukuran' => 'XL', 'nama' => 'Tas',       'urutan' => 1],
            ['ukuran' => 'XL', 'nama' => 'Koper',    'urutan' => 2],
            ['ukuran' => 'XL', 'nama' => 'Carrier',      'urutan' => 3],
            ['ukuran' => 'XL', 'nama' => 'Lainnya',     'urutan' => 99],

            // Kategori Gadget
            ['ukuran' => 'Gadget', 'nama' => 'Tas',           'urutan' => 1],
            ['ukuran' => 'Gadget', 'nama' => 'Kamera Laptop', 'urutan' => 2],
            ['ukuran' => 'Gadget', 'nama' => 'Smartphone',    'urutan' => 3],
            ['ukuran' => 'Gadget', 'nama' => 'Kamera',        'urutan' => 4],
            ['ukuran' => 'Gadget', 'nama' => 'Lainnya',       'urutan' => 99],
        ];

        foreach ($data as $item) {
            JenisBarang::create($item);
        }
    }
}
