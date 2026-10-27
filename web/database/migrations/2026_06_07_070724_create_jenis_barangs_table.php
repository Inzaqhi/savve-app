<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('ukuran', 10); // S, M, L, XL, Gadget
            $table->string('nama');      // Parfum, Vape, Jaket, dll
            $table->boolean('is_active')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_barangs');
    }
};
