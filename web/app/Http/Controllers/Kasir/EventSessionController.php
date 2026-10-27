<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventSessionController extends Controller
{
    public function index()
    {
        // Auto nonaktifkan event expired
        Event::where('status', 'aktif')
            ->where('tanggal_selesai', '<', today())
            ->each(function ($event) {
                $event->update(['status' => 'nonaktif']);
                \App\Models\Transaksi::where('event_id', $event->id)
                    ->where('status', 'dititip')
                    ->update(['status' => 'terlambat']);
            });

        $events = Event::where('status', 'aktif')
            ->orderBy('tanggal_mulai')
            ->get();

        $eventAktif = null;
        if (session('kasir_event_id')) {
            $eventAktif = Event::find(session('kasir_event_id'));
        }

        return view('kasir.event.select', compact('events', 'eventAktif'));
    }

    public function pilih(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $event = Event::findOrFail($request->event_id);

        if ($event->status !== 'aktif') {
            return back()->with('error', 'Event ini sudah tidak aktif.');
        }

        session([
            'kasir_event_id'   => $event->id,
            'kasir_event_nama' => $event->nama_event,
            'kasir_event_kode' => $event->kode_event,
        ]);

        return redirect()->route('kasir.dashboard')
            ->with('success', "Event {$event->nama_event} Berhasil dipilih.");
    }

    public function ganti(Request $request)
    {
        session()->forget(['kasir_event_id', 'kasir_event_nama', 'kasir_event_kode']);

        return redirect()->route('kasir.event.select')
            ->with('info', 'Silakan pilih event baru.');
    }
}
