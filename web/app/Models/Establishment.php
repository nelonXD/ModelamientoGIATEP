<?php

namespace App\Models;

use Database\Factories\EstablishmentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Establishment extends Model
{
    /** @use HasFactory<EstablishmentFactory> */
    use HasFactory;

    protected $fillable = ['type', 'name', 'is_active'];

    public function getTable(): string
    {
        return $this->usesOriginalSchema() ? 'dependencia' : parent::getTable();
    }

    public function getKeyName(): string
    {
        return $this->usesOriginalSchema() ? 'Id_Dependencia' : parent::getKeyName();
    }

    protected function usesOriginalSchema(): bool
    {
        return $this->getConnection()->getDriverName() === 'mysql'
            && $this->getConnection()->getSchemaBuilder()->hasTable('dependencia');
    }

    protected function id(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Id_Dependencia'] ?? null) : $value);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Nombre_Dependencia'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Nombre_Dependencia' => $value] : ['name' => $value],
        );
    }

    protected function type(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): string => $this->usesOriginalSchema() ? 'Dependencia' : (string) $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? [] : ['type' => $value],
        );
    }

    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): bool => $this->usesOriginalSchema() ? true : (bool) $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? [] : ['is_active' => $value],
        );
    }

    public function users(): BelongsToMany
    {
        if ($this->usesOriginalSchema()) {
            return $this->belongsToMany(User::class, 'usuario', 'Id_Dependencia', 'Rut_Usuario', 'Id_Dependencia', 'Rut_Usuario');
        }

        return $this->belongsToMany(User::class);
    }

    public function registrationRequests(): HasMany
    {
        if ($this->usesOriginalSchema()) {
            return $this->newHasMany(RegistrationRequest::query(), $this, 'Id_Dependencia_Solicitada', $this->getKeyName());
        }

        return $this->hasMany(RegistrationRequest::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
