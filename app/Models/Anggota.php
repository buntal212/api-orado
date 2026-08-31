<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'club_id', 'name', 'nama', 'email', 'no_hp', 'alamat', 'foto', 'nik', 'tanggal_lahir', 'jenis_kelamin', 'kelompok_jabatan', 'jabatan', 'flag'])]
class Anggota extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function iurans(): HasMany
    {
        return $this->hasMany(Iuran::class);
    }
}
