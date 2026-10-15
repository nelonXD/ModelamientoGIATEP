<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireForm extends Model
{
    protected $guarded = [];

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionnaireQuestion::class);
    }
}
