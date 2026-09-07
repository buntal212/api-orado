<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengurusNotification extends Model
{
    protected $fillable = [
        'title',
        'body',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
