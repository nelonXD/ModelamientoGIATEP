<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireQuestion extends Model
{
    protected $guarded = [];

    public function options(): HasMany
    {
        return $this->hasMany(QuestionnaireOption::class);
    }

    protected function casts(): array
    {
        return ['obligatoria' => 'boolean', 'activa' => 'boolean', 'configuracion' => 'array'];
    }
}
