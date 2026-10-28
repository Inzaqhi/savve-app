<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Jenis barang XXL menjadi Koper dan Carrier.
 * Riwayat transaksi tidak terpengaruh karena nama barang disimpan langsung di detail transaksi.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->setJenis([['Koper', 1], ['Carrier', 2]]);
    }

    public function down(): void
    {
        $this->setJenis([['Koper Besar', 1], ['Carrier Besar', 2], ['Kardus', 3], ['Lainnya', 99]]);
    }

    private function setJenis(array $jenis): void
    {
        $now = now();
        DB::table('jenis_barangs')->where('ukuran', 'XXL')->delete();
        DB::table('jenis_barangs')->insert(array_map(fn ($j) => [
            'ukuran'     => 'XXL',
            'nama'       => $j[0],
            'urutan'     => $j[1],
            'is_active'  => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $jenis));
    }
};
