<?php

namespace App\Models;

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Investigation extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return OriginalGiatepSchema::isActive() ? 'investigacion' : parent::getTable();
    }

    public function getKeyName(): string
    {
        return OriginalGiatepSchema::isActive() ? 'Codigo_Investigacion' : parent::getKeyName();
    }

    public function usesTimestamps(): bool
    {
        return OriginalGiatepSchema::isActive() ? false : parent::usesTimestamps();
    }

    protected function usesOriginalSchema(): bool
    {
        return OriginalGiatepSchema::isActive();
    }

    protected function id(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Codigo_Investigacion'] ?? null) : $value);
    }

    protected function folio(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Folio'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Folio' => $value] : ['folio' => $value],
        );
    }

    protected function occupationalCaseId(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Id_Caso'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Id_Caso' => $value] : ['occupational_case_id' => $value],
        );
    }

    protected function ownerId(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Rut_Usuario'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Rut_Usuario' => $value] : ['owner_id' => $value],
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Estado'] ?? 'En investigación') : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Estado' => $value] : ['status' => $value],
        );
    }

    protected function finalStory(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Relato_Final'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Relato_Final' => $value] : ['final_story' => $value],
        );
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): mixed {
                $date = $this->usesOriginalSchema() ? ($attributes['Fecha_Creacion'] ?? null) : $value;

                return $date ? Carbon::parse($date) : null;
            },
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Fecha_Creacion' => $value] : ['created_at' => $value],
        );
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): mixed {
                $date = $this->usesOriginalSchema() ? ($attributes['Fecha_Creacion'] ?? null) : $value;

                return $date ? Carbon::parse($date) : null;
            },
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Fecha_Creacion' => $value] : ['updated_at' => $value],
        );
    }

    public function occupationalCase(): BelongsTo
    {
        return $this->belongsTo(OccupationalCase::class, $this->usesOriginalSchema() ? 'Id_Caso' : 'occupational_case_id', $this->usesOriginalSchema() ? 'Id_Caso' : 'id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, $this->usesOriginalSchema() ? 'Rut_Usuario' : 'owner_id', $this->usesOriginalSchema() ? 'Rut_Usuario' : 'id');
    }

    public function facts(): HasMany
    {
        return $this->hasMany(InvestigationFact::class, 'investigation_id', $this->usesOriginalSchema() ? 'Codigo_Investigacion' : 'id');
    }

    public function causalNodes(): HasMany
    {
        return $this->hasMany(CausalNode::class, 'investigation_id', $this->usesOriginalSchema() ? 'Codigo_Investigacion' : 'id');
    }

    public function measures(): HasMany
    {
        return $this->hasMany(CorrectiveMeasure::class, 'investigation_id', $this->usesOriginalSchema() ? 'Codigo_Investigacion' : 'id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(InvestigationInterview::class, $this->usesOriginalSchema() ? 'Codigo_Investigacion' : 'investigation_id', $this->usesOriginalSchema() ? 'Codigo_Investigacion' : 'id');
    }

    public function causeFactors(): BelongsToMany
    {
        return $this->belongsToMany(
            CauseFactor::class,
            'investigation_cause_factor',
            'investigation_id',
            'cause_factor_id',
            $this->usesOriginalSchema() ? 'Codigo_Investigacion' : 'id',
            'id',
        )->withPivot(['origen', 'fundamento', 'validated_by', 'validated_at'])->withTimestamps();
    }

    protected function casts(): array
    {
        return ['ai_analyzed_at' => 'datetime'];
    }
}
