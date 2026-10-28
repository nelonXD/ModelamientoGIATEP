<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\RolePermissionMap;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['rut', 'name', 'email', 'job_title', 'phone', 'status', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function getTable(): string
    {
        return $this->usesOriginalSchema() ? 'usuario' : parent::getTable();
    }

    public function getKeyName(): string
    {
        return $this->usesOriginalSchema() ? 'Rut_Usuario' : parent::getKeyName();
    }

    public function getIncrementing(): bool
    {
        return $this->usesOriginalSchema() ? false : parent::getIncrementing();
    }

    public function usesTimestamps(): bool
    {
        return $this->usesOriginalSchema() ? false : parent::usesTimestamps();
    }

    public function getKeyType(): string
    {
        return $this->usesOriginalSchema() ? 'string' : parent::getKeyType();
    }

    public function getAuthPasswordName(): string
    {
        return $this->usesOriginalSchema() ? 'Contrasena_Hash' : parent::getAuthPasswordName();
    }

    public static function columnFor(string $attribute): string
    {
        if (! (new self)->usesOriginalSchema()) {
            return $attribute;
        }

        return [
            'rut' => 'Rut_Usuario',
            'name' => 'Nombre',
            'email' => 'Correo',
            'password' => 'Contrasena_Hash',
            'status' => 'Estado',
            'phone' => 'Telefono',
            'job_title' => 'Cargo',
        ][$attribute] ?? $attribute;
    }

    protected function usesOriginalSchema(): bool
    {
        return $this->getConnection()->getDriverName() === 'mysql'
            && $this->getConnection()->getSchemaBuilder()->hasTable('usuario');
    }

    protected function id(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Rut_Usuario'] ?? null) : $value);
    }

    protected function rut(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Rut_Usuario'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Rut_Usuario' => $value] : ['rut' => $value],
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema()
                ? trim(($attributes['Nombre'] ?? '').' '.($attributes['Apellido'] ?? ''))
                : $value,
            set: function (mixed $value): array {
                if (! $this->usesOriginalSchema()) {
                    return ['name' => $value];
                }

                $parts = preg_split('/\s+/', trim((string) $value), 2) ?: [];

                return ['Nombre' => $parts[0] ?? '', 'Apellido' => $parts[1] ?? ''];
            },
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Correo'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Correo' => $value] : ['email' => $value],
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Contrasena_Hash'] ?? null) : $value,
            set: function (mixed $value): array {
                if ($this->usesOriginalSchema()) {
                    return ['Contrasena_Hash' => $value];
                }

                $password = (string) $value;

                return ['password' => password_get_info($password)['algoName'] !== 'unknown' ? $password : Hash::make($password)];
            },
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Estado'] ?? 'active') : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Estado' => $value] : ['status' => $value],
        );
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Telefono'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Telefono' => $value] : ['phone' => $value],
        );
    }

    protected function jobTitle(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Cargo'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Cargo' => $value] : ['job_title' => $value],
        );
    }

    public function roles(): BelongsToMany
    {
        if ($this->usesOriginalSchema()) {
            return $this->belongsToMany(Role::class, 'rol_usuario', 'Rut_Usuario', 'Id_Rol', 'Rut_Usuario', 'Id_Rol');
        }

        return $this->belongsToMany(Role::class);
    }

    public function establishments(): BelongsToMany
    {
        if ($this->usesOriginalSchema()) {
            return $this->belongsToMany(Establishment::class, 'usuario', 'Rut_Usuario', 'Id_Dependencia', 'Rut_Usuario', 'Id_Dependencia');
        }

        return $this->belongsToMany(Establishment::class);
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, RolePermissionMap::forRoles($this->roles->pluck('slug')), true);
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles->contains('slug', $slug);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
