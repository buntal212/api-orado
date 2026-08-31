<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['club_id', 'anggota_id', 'periode', 'nominal', 'tanggal_bayar', 'catatan'])]
class Iuran extends Model
{
    protected function casts(): array
    {
        return ['periode' => 'date', 'tanggal_bayar' => 'date'];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }
}
