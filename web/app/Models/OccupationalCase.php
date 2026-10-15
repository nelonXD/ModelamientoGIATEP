<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OccupationalCase extends Model
{
    protected $guarded = [];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(CaseEvidence::class);
    }

    public function investigation(): HasOne
    {
        return $this->hasOne(Investigation::class);
    }

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'hire_date' => 'date', 'event_date' => 'date', 'details' => 'array'];
    }
}
