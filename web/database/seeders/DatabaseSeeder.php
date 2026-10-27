<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            KategoriBarangSeeder::class,
            JenisBarangSeeder::class,
        ]);

        // Seed default active event for E2E testing
        $event = \App\Models\Event::firstOrCreate(
            ['kode_event' => 'TEST'],
            [
                'nama_event' => 'Event Test E2E',
                'tanggal_mulai' => now()->subDays(1)->format('Y-m-d'),
                'tanggal_selesai' => now()->addDays(5)->format('Y-m-d'),
                'status' => 'aktif',
            ]
        );

        foreach (['S', 'M', 'L', 'XL', 'Gadget'] as $ukuran) {
            \App\Models\Tarif::firstOrCreate(
                ['event_id' => $event->id, 'ukuran' => $ukuran],
                ['harga' => 10000]
            );
        }
    }
}
