<?php

namespace App\Helpers;

use App\Models\Transaksi;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class NomorTransaksi
{
    // ── Static Helpers ──

    public static function generateNomor(Event $event): string
    {
        $kodeEvent = $event->kode_event ?? 'EVT';
        $prefix    = "SVV-{$kodeEvent}-";

        // lockForUpdate() mengunci baris-baris yang match sampai
        // transaction di controller selesai (commit/rollback),
        // jadi request lain yang barengan harus antri di sini.
        $lastNomor = static::where('event_id', $event->id)
            ->where('nomor_transaksi', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(nomor_transaksi, -4) AS UNSIGNED) DESC')
            ->value('nomor_transaksi');

        $lastUrutan = $lastNomor ? (int) substr($lastNomor, -4) : 0;
        $urutan     = str_pad($lastUrutan + 1, 4, '0', STR_PAD_LEFT);

        return $prefix . $urutan;
    }
}
