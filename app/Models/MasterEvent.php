<?php

namespace App\Models;

use Database\Factories\MasterEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kode_event', 'nama_event', 'deskripsi', 'lokasi', 'tanggal_mulai', 'tanggal_selesai', 'pendaftaran_mulai', 'pendaftaran_selesai', 'kuota_peserta', 'biaya_pendaftaran', 'poster', 'status'])]
class MasterEvent extends Model
{
    /** @use HasFactory<MasterEventFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'pendaftaran_mulai' => 'date',
            'pendaftaran_selesai' => 'date',
        ];
    }

    public function pendaftarans(): HasMany
    {
        return $this->hasMany(PendaftaranEventHeader::class);
    }
}
