<?php

namespace App\Models;

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireForm extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return OriginalGiatepSchema::isActive() ? 'formulario_declaracion' : parent::getTable();
    }

    public function getKeyName(): string
    {
        return OriginalGiatepSchema::isActive() ? 'Id_Formulario' : parent::getKeyName();
    }

    public function usesTimestamps(): bool
    {
        return OriginalGiatepSchema::isActive() ? false : parent::usesTimestamps();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionnaireQuestion::class, OriginalGiatepSchema::isActive() ? 'Id_Formulario' : 'questionnaire_form_id');
    }

    protected function tipo(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => OriginalGiatepSchema::isActive() ? 'adapted' : $value);
    }

    protected function estado(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive() ? ($attributes['Estado'] ?? null) : $value);
    }

    protected function version(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive() ? ($attributes['Version'] ?? null) : $value);
    }
}
