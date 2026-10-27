<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi')->unique();
            $table->foreignId('event_id')->constrained('events');
            $table->foreignId('kasir_id')->constrained('users');
            $table->string('nama_penitip');
            $table->string('no_whatsapp');
            $table->enum('status', ['dititip', 'sudah_diambil'])->default('dititip');
            $table->timestamp('waktu_penitipan')->useCurrent();
            $table->timestamp('waktu_pengambilan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
