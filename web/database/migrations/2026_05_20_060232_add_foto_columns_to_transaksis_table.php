<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->string('foto_penitipan')->nullable()->after('waktu_penitipan');
            $table->string('foto_pengambilan')->nullable()->after('waktu_pengambilan');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['foto_penitipan', 'foto_pengambilan']);
        });
    }
};
