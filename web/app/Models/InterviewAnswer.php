<?php

namespace App\Models;

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewAnswer extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return OriginalGiatepSchema::isActive() ? 'respuesta_declaracion' : parent::getTable();
    }

    public function getKeyName(): string
    {

        return OriginalGiatepSchema::isActive() ? 'Id_Respuesta' : parent::getKeyName();
    }

    public function getIncrementing(): bool
    {

        return OriginalGiatepSchema::isActive() ? true : parent::getIncrementing();
    }

    public function usesTimestamps(): bool
    {
        return OriginalGiatepSchema::isActive() ? false : parent::usesTimestamps();
    }

    protected function investigationInterviewId(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive() ? ($attributes['Id_Declaracion'] ?? null) : $value);
    }

    protected function questionKey(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive()
            ? QuestionnaireQuestion::query()->whereKey($attributes['Id_Pregunta'] ?? null)->value('Variable')
            : $value);
    }

    protected function value(): Attribute
    {
        return Attribute::get(function (mixed $value, array $attributes): mixed {
            $storedValue = OriginalGiatepSchema::isActive()
                ? ($attributes['Texto_Respuesta'] ?? null)
                : $value;

            return is_string($storedValue)
                ? (json_decode($storedValue, true) ?? $storedValue)
                : $storedValue;
        });
    }

    public function question(): BelongsTo
    {
        $foreignKey = OriginalGiatepSchema::isActive() ? 'Id_Pregunta' : 'questionnaire_question_id';

        return $this->belongsTo(QuestionnaireQuestion::class, $foreignKey);
    }

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public function getValueAttribute($value)
    {
        $storedValue = OriginalGiatepSchema::isActive()
            ? ($this->attributes['Texto_Respuesta'] ?? null)
            : $value;

        if (! is_string($storedValue)) {
            return $storedValue;
        }

        $decoded = json_decode($storedValue, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $storedValue;
    }
}
