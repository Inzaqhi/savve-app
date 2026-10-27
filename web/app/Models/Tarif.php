<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    protected $fillable = [
        'event_id',
        'ukuran',
        'harga',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
