<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'nama_event',
        'kode_event',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function tarifs()
    {
        return $this->hasMany(Tarif::class);
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }
}
