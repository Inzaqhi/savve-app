<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Transaksi;
use Illuminate\Console\Command;

class UpdateStatusEvent extends Command
{
    protected $signature   = 'event:update-status';
    protected $description = 'Nonaktifkan event expired & tandai transaksi terlambat';

    public function handle(): int
    {
        // FIX #13: Hapus blok duplikat kedua yang memproses event nonaktif ulang.
        // Sebelumnya ada 2 blok: (1) nonaktifkan event expired + tandai transaksinya,
        // lalu (2) cari semua event nonaktif dan tandai transaksinya lagi.
        // Transaksi dari event yang baru dinonaktifkan di blok (1) diproses dua kali.
        // Sekarang hanya satu blok yang konsisten: cari event expired yang masih aktif,
        // nonaktifkan, lalu tandai transaksinya — semuanya dalam satu query yang rapi.

        // Step 1: Temukan event yang tanggal selesainya sudah lewat tapi statusnya masih aktif
        $expiredEvents = Event::where('status', 'aktif')
            ->where('tanggal_selesai', '<', today())
            ->get();

        if ($expiredEvents->isEmpty()) {
            $this->info('Tidak ada event yang perlu diperbarui.');
            return self::SUCCESS;
        }

        $expiredEventIds = $expiredEvents->pluck('id');

        // Step 2: Nonaktifkan semua event expired sekaligus (bulk update)
        Event::whereIn('id', $expiredEventIds)
            ->update(['status' => 'nonaktif']);

        // Step 3: Tandai transaksi yang masih 'dititip' di event-event tersebut sebagai terlambat
        $updatedCount = Transaksi::whereIn('event_id', $expiredEventIds)
            ->where('status', 'dititip')
            ->update(['status' => 'terlambat']);

        foreach ($expiredEvents as $event) {
            $this->info("Event '{$event->nama_event}' dinonaktifkan.");
        }

        $this->info("{$updatedCount} transaksi ditandai terlambat.");
        $this->info("{$expiredEvents->count()} event dinonaktifkan.");

        return self::SUCCESS;
    }
}
