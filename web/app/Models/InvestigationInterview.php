<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestigationInterview extends Model
{
    protected $guarded = [];

    public function answers(): HasMany
    {
        return $this->hasMany(InterviewAnswer::class);
    }

    protected function casts(): array
    {
        return ['interview_date' => 'date'];
    }
}
