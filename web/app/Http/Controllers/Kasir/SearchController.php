<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return view('kasir.search', compact('query'))->with([
                'transaksis' => collect(),
            ]);
        }

        $transaksis = Transaksi::with(['event', 'details'])
            ->where('kasir_id', auth()->id())
            ->where(function ($q) use ($query) {
                $q->where('nama_penitip', 'like', "%{$query}%")
                    ->orWhere('nomor_transaksi', 'like', "%{$query}%")
                    ->orWhere('no_whatsapp', 'like', "%{$query}%");
            })
            ->latest()
            ->take(10)
            ->get();

        return view('kasir.search', compact('query', 'transaksis'));
    }
}
