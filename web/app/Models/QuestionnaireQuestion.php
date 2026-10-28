<?php

namespace App\Models;

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireQuestion extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return OriginalGiatepSchema::isActive() ? 'pregunta_declaracion' : parent::getTable();
    }

    public function getKeyName(): string
    {
        return OriginalGiatepSchema::isActive() ? 'Id_Pregunta' : parent::getKeyName();
    }

    public function usesTimestamps(): bool
    {
        return OriginalGiatepSchema::isActive() ? false : parent::usesTimestamps();
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionnaireOption::class);
    }

    protected function variable(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive() ? ($attributes['Variable'] ?? null) : $value,
            set: fn (mixed $value): array => OriginalGiatepSchema::isActive() ? ['Variable' => $value] : ['variable' => $value],
        );
    }

    protected function textoPregunta(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive() ? ($attributes['Texto_Pregunta'] ?? null) : $value,
            set: fn (mixed $value): array => OriginalGiatepSchema::isActive() ? ['Texto_Pregunta' => $value] : ['texto' => $value],
        );
    }

    protected function casts(): array
    {
        return ['obligatoria' => 'boolean', 'activa' => 'boolean', 'configuracion' => 'array'];
    }
}
