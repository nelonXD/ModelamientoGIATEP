<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investigation extends Model
{
    protected $guarded = [];

    public function occupationalCase(): BelongsTo
    {
        return $this->belongsTo(OccupationalCase::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }

    public function facts(): HasMany
    {
        return $this->hasMany(InvestigationFact::class);
    }

    public function causalNodes(): HasMany
    {
        return $this->hasMany(CausalNode::class);
    }

    public function measures(): HasMany
    {
        return $this->hasMany(CorrectiveMeasure::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(InvestigationInterview::class);
    }

    public function causeFactors(): BelongsToMany
    {
        return $this->belongsToMany(CauseFactor::class, 'investigation_cause_factor')->withPivot(['origen', 'fundamento', 'validated_by', 'validated_at'])->withTimestamps();
    }

    protected function casts(): array
    {
        return ['ai_analyzed_at' => 'datetime'];
    }
}
