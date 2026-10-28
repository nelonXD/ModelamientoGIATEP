<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    protected $fillable = ['slug', 'name'];

    public function getTable(): string
    {
        return $this->usesOriginalSchema() ? 'rol' : parent::getTable();
    }

    public function getKeyName(): string
    {
        return $this->usesOriginalSchema() ? 'Id_Rol' : parent::getKeyName();
    }

    protected function usesOriginalSchema(): bool
    {
        return $this->getConnection()->getDriverName() === 'mysql'
            && $this->getConnection()->getSchemaBuilder()->hasTable('rol');
    }

    protected function id(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Id_Rol'] ?? null) : $value);
    }

    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema()
                ? ($attributes['Codigo_Rol'] ?? str($attributes['Nombre_Rol'] ?? '')->slug()->toString())
                : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Codigo_Rol' => $value] : ['slug' => $value],
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Nombre_Rol'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Nombre_Rol' => $value] : ['name' => $value],
        );
    }

    public function users(): BelongsToMany
    {
        if ($this->usesOriginalSchema()) {
            return $this->belongsToMany(User::class, 'rol_usuario', 'Id_Rol', 'Rut_Usuario', 'Id_Rol', 'Rut_Usuario');
        }

        return $this->belongsToMany(User::class);
    }
}
