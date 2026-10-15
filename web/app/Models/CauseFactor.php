<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CauseFactor extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
