<?php

namespace App\Models;

use Database\Factories\MasterJabatanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJabatan extends Model
{
    /** @use HasFactory<MasterJabatanFactory> */
    use HasFactory;

    protected $fillable = ['nama'];
}
