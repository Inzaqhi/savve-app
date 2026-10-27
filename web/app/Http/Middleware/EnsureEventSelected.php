<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Event;

class EnsureEventSelected
{
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->routeIs('kasir.event.select') ||
            $request->routeIs('kasir.event.pilih') ||
            $request->routeIs('kasir.event.ganti')
        ) {
            return $next($request);
        }

        $eventId = session('kasir_event_id');

        if (!$eventId) {
            return redirect()->route('kasir.event.select')
                ->with('info', 'Silakan pilih event terlebih dahulu.');
        }

        $event = Event::find($eventId);
        if (!$event || $event->status !== 'aktif') {
            session()->forget(['kasir_event_id', 'kasir_event_nama', 'kasir_event_kode']);
            return redirect()->route('kasir.event.select')
                ->with('warning', 'Event sudah tidak aktif. Pilih event lain.');
        }

        view()->share('eventAktifKasir', $event);

        return $next($request);
    }
}
