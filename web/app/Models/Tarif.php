<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    /** Semua kategori ukuran penitipan, sesuai urutan tampil. */
    public const UKURAN = ['S', 'M', 'L', 'XL', 'XXL', 'Gadget'];

    /** Nama lengkap tiap kategori ukuran. */
    public const LABEL = [
        'S'      => 'Small',
        'M'      => 'Medium',
        'L'      => 'Large',
        'XL'     => 'Extra Large',
        'XXL'    => 'Double Extra Large',
        'Gadget' => 'Gadget',
    ];

    protected $fillable = [
        'event_id',
        'ukuran',
        'harga',
    ];

    /**
     * Ukuran yang punya tarif di event ini (event lama mungkin belum punya tarif XXL).
     * Tanpa data tarif, semua ukuran dikembalikan.
     */
    public static function tersedia($tarifs): array
    {
        if (empty($tarifs) || count($tarifs) === 0) {
            return self::UKURAN;
        }

        return array_values(array_filter(self::UKURAN, fn ($u) => isset($tarifs[$u])));
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
