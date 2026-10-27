<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->setUkuran(['S', 'M', 'L', 'XL', 'Gadget']);
    }

    public function down(): void
    {
        $this->setUkuran(['S', 'M', 'L', 'XL']);
    }

    private function setUkuran(array $values): void
    {
        // SQLite (aplikasi desktop) tidak mengenal ALTER ... MODIFY, jadi pakai change()
        if (DB::getDriverName() === 'sqlite') {
            foreach (['tarifs', 'detail_transaksis'] as $tableName) {
                Schema::table($tableName, function (Blueprint $table) use ($values) {
                    $table->enum('ukuran', $values)->change();
                });
            }
            return;
        }

        $list = "'" . implode("', '", $values) . "'";
        DB::statement("ALTER TABLE tarifs MODIFY ukuran ENUM($list) NOT NULL");
        DB::statement("ALTER TABLE detail_transaksis MODIFY ukuran ENUM($list) NOT NULL");
    }
};
