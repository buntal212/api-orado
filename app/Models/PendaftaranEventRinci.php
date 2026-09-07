<?php

namespace App\Models;

use Database\Factories\PendaftaranEventRinciFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pendaftaran_event_header_id', 'nama_peserta', 'nik_atlet_satu', 'nama_atlet_satu', 'tanggal_lahir_atlet_satu', 'jenis_kelamin_atlet_satu', 'no_hp_atlet_satu', 'nik_atlet_dua', 'nama_atlet_dua', 'tanggal_lahir_atlet_dua', 'jenis_kelamin_atlet_dua', 'no_hp_atlet_dua', 'biaya_pendaftaran', 'status'])]
class PendaftaranEventRinci extends Model
{
    /** @use HasFactory<PendaftaranEventRinciFactory> */
    use HasFactory;

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranEventHeader::class, 'pendaftaran_event_header_id');
    }
}
