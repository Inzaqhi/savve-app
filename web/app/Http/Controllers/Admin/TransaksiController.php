<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $events = \App\Models\Event::orderBy('nama_event')->get();

        $query = Transaksi::with(['event', 'kasir', 'details']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_penitip', 'like', "%{$search}%")
                    ->orWhere('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhere('no_whatsapp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter rentang tanggal
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('waktu_penitipan', [
                $request->tanggal_mulai . ' 00:00:00',
                $request->tanggal_selesai . ' 23:59:59',
            ]);
        } elseif ($request->filled('tanggal_mulai')) {
            $query->whereDate('waktu_penitipan', '>=', $request->tanggal_mulai);
        } elseif ($request->filled('tanggal_selesai')) {
            $query->whereDate('waktu_penitipan', '<=', $request->tanggal_selesai);
        } elseif ($request->filled('tanggal')) {
            $query->whereDate('waktu_penitipan', $request->tanggal);
        }

        $transaksis = $query->latest('waktu_penitipan')->get();

        return view('admin.transaksis.index', compact('transaksis', 'events'));
    }

    public function show(Transaksi $transaksi)
    {
        $transaksi->load(['event', 'details', 'kasir']);
        return view('admin.transaksis.show', compact('transaksi'));
    }

    public function destroy(Transaksi $transaksi)
    {
        $transaksi->details()->delete();
        $transaksi->delete();

        return redirect()->route('admin.transaksis.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
