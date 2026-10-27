<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Data awal untuk aplikasi desktop: akun default dan master barang,
 * tanpa event uji coba E2E dari DatabaseSeeder.
 */
class DesktopSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            KategoriBarangSeeder::class,
            JenisBarangSeeder::class,
        ]);
    }
}
