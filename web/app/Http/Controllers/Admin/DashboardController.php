<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Transaksi;

class DashboardController extends Controller
{
    public function index()
    {
        // Auto nonaktifkan event expired & tandai transaksi terlambat
        Event::where('status', 'aktif')
            ->where('tanggal_selesai', '<', today())
            ->each(function ($event) {
                $event->update(['status' => 'nonaktif']);
                Transaksi::where('event_id', $event->id)
                    ->where('status', 'dititip')
                    ->update(['status' => 'terlambat']);
            });

        $totalEventAktif  = Event::where('status', 'aktif')->count();
        $transaksiHariIni = Transaksi::whereDate('created_at', today())->count();

        // Fix: belumDiambil mencakup dititip + terlambat
        $belumDiambil     = Transaksi::whereIn('status', ['dititip', 'terlambat'])->count();
        $sudahDiambil     = Transaksi::where('status', 'sudah_diambil')->count();

        $transaksiTerbaru = Transaksi::with(['details', 'event'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalEventAktif',
            'transaksiHariIni',
            'belumDiambil',
            'sudahDiambil',
            'transaksiTerbaru'
        ));
    }
}
