<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kategori ukuran XXL: kolom tarifs.ukuran (enum) dan jenis barang bawaan untuk XXL.
 */
return new class extends Migration
{
    private const JENIS_XXL = [
        ['nama' => 'Koper Besar', 'urutan' => 1],
        ['nama' => 'Carrier Besar', 'urutan' => 2],
        ['nama' => 'Kardus', 'urutan' => 3],
        ['nama' => 'Lainnya', 'urutan' => 99],
    ];

    public function up(): void
    {
        $this->setUkuran(['S', 'M', 'L', 'XL', 'XXL', 'Gadget']);

        if (DB::table('jenis_barangs')->where('ukuran', 'XXL')->doesntExist()) {
            $now = now();
            DB::table('jenis_barangs')->insert(array_map(fn ($j) => $j + [
                'ukuran'     => 'XXL',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], self::JENIS_XXL));
        }
    }

    public function down(): void
    {
        DB::table('jenis_barangs')->where('ukuran', 'XXL')->delete();
        DB::table('tarifs')->where('ukuran', 'XXL')->delete();
        $this->setUkuran(['S', 'M', 'L', 'XL', 'Gadget']);
    }

    private function setUkuran(array $values): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('tarifs', function (Blueprint $table) use ($values) {
                $table->enum('ukuran', $values)->change();
            });
            return;
        }

        $list = "'" . implode("', '", $values) . "'";
        DB::statement("ALTER TABLE tarifs MODIFY ukuran ENUM($list) NOT NULL");
    }
};
