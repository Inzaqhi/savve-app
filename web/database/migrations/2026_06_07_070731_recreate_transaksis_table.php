<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus data lama
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('detail_transaksis');
        Schema::dropIfExists('transaksis');
        Schema::enableForeignKeyConstraints();

        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi')->unique();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('kasir_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_penitip');
            $table->string('no_whatsapp');
            $table->enum('metode_bayar', ['QRIS', 'Cash', 'Web'])->default('Cash');
            $table->enum('status', ['dititip', 'terlambat', 'sudah_diambil'])->default('dititip');
            $table->string('foto_penitipan')->nullable();
            $table->string('foto_pengambilan')->nullable();
            $table->timestamp('waktu_penitipan')->nullable();
            $table->timestamp('waktu_pengambilan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
