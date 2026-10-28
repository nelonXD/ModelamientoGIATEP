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

    protected function value(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive()
            ? (($attributes['Texto_Respuesta'] ?? null) === null ? null : (json_decode($attributes['Texto_Respuesta'], true) ?? $attributes['Texto_Respuesta']))
            : $value);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireQuestion::class, 'questionnaire_question_id');
    }

    protected function casts(): array
    {
        return ['value' => 'array'];
    }
}
