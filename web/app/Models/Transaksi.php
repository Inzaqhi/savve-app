<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $fillable = [
        'nomor_transaksi',
        'event_id',
        'kasir_id',
        'nama_penitip',
        'no_whatsapp',
        'metode_bayar',
        'status',
        'foto_penitipan',
        'foto_pengambilan',
        'waktu_penitipan',
        'waktu_pengambilan',
    ];

    protected $casts = [
        'waktu_penitipan'   => 'datetime',
        'waktu_pengambilan' => 'datetime',
    ];

    // ── Relationships ──

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function details()
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    // ── Accessors ──

    public function getTotalHargaAttribute(): float
    {
        return $this->details->sum('subtotal');
    }

    // ── Static Helpers ──

    public static function generateNomor(Event $event): string
    {
        $kodeEvent = $event->kode_event ?? 'EVT';
        $prefix    = "SVV-{$kodeEvent}-";

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
