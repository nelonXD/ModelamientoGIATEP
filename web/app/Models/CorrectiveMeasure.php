<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectiveMeasure extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'ai_generated' => 'boolean'];
    }
}
