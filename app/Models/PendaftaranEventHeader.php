<?php

namespace App\Models;

use Database\Factories\PendaftaranEventHeaderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['master_event_id', 'kode_event', 'kode_pendaftaran', 'nama_tim', 'nama_pendaftar', 'no_hp', 'email', 'jumlah_peserta', 'total_biaya', 'status_pendaftaran', 'status_pembayaran', 'catatan'])]
class PendaftaranEventHeader extends Model
{
    /** @use HasFactory<PendaftaranEventHeaderFactory> */
    use HasFactory;

    public function event(): BelongsTo
    {
        return $this->belongsTo(MasterEvent::class, 'master_event_id');
    }

    public function rincis(): HasMany
    {
        return $this->hasMany(PendaftaranEventRinci::class);
    }
}
