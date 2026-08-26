<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'kode_club', 'nama_club', 'alamat', 'kelurahan', 'kecamatan', 'status', 'catatan'])]
class Club extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
