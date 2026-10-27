<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisBarang extends Model
{
    protected $fillable = ['ukuran', 'nama', 'is_active', 'urutan'];

    public static function byUkuran(): array
    {
        return static::where('is_active', true)
            ->orderBy('urutan')
            ->get()
            ->groupBy('ukuran')
            ->toArray();
    }
}
