<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogOption extends Model
{
    protected $fillable = ['category', 'value', 'label', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
