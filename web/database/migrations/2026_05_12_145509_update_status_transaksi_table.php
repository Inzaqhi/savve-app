<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tarifs MODIFY ukuran ENUM('S', 'M', 'L', 'XL', 'Gadget') NOT NULL");
        DB::statement("ALTER TABLE detail_transaksis MODIFY ukuran ENUM('S', 'M', 'L', 'XL', 'Gadget') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tarifs MODIFY ukuran ENUM('S', 'M', 'L', 'XL') NOT NULL");
        DB::statement("ALTER TABLE detail_transaksis MODIFY ukuran ENUM('S', 'M', 'L', 'XL') NOT NULL");
    }
};
