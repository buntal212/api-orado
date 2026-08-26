<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'club_id', 'name', 'nama', 'email', 'no_hp', 'alamat', 'foto', 'nik', 'kelompok_jabatan', 'jabatan', 'flag'])]
class Anggota extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
