<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return view('admin.search', compact('query'))->with([
                'transaksis' => collect(),
                'events'     => collect(),
                'kasirs'     => collect(),
            ]);
        }

        $transaksis = Transaksi::with(['event', 'kasir', 'details'])
            ->where('nama_penitip', 'like', "%{$query}%")
            ->orWhere('nomor_transaksi', 'like', "%{$query}%")
            ->orWhere('no_whatsapp', 'like', "%{$query}%")
            ->latest()
            ->take(10)
            ->get();

        $events = Event::where('nama_event', 'like', "%{$query}%")
            ->take(5)
            ->get();

        $kasirs = User::where('role', 'kasir')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(5)
            ->get();

        return view('admin.search', compact('query', 'transaksis', 'events', 'kasirs'));
    }
}
