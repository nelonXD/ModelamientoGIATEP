<?php

namespace App\Models;

use App\Support\OriginalGiatepSchema;
use Database\Factories\RegistrationRequestFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RegistrationRequest extends Model
{
    /** @use HasFactory<RegistrationRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'rut', 'pending_key', 'name', 'job_title', 'establishment_id', 'requested_role',
        'email', 'phone', 'password', 'status', 'rejection_reason', 'created_at',
        'resolved_by', 'resolved_at',
    ];

    public function getTable(): string
    {
        return OriginalGiatepSchema::isActive() ? 'solicitud_acceso' : parent::getTable();
    }

    public function getKeyName(): string
    {
        return OriginalGiatepSchema::isActive() ? 'Id_Solicitud' : parent::getKeyName();
    }

    public function usesTimestamps(): bool
    {
        return OriginalGiatepSchema::isActive() ? false : parent::usesTimestamps();
    }

    protected function usesOriginalSchema(): bool
    {
        return OriginalGiatepSchema::isActive();
    }

    protected function id(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Id_Solicitud'] ?? null) : $value);
    }

    protected function rut(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Rut_Solicitante'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Rut_Solicitante' => $value] : ['rut' => $value],
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Nombre_Solicitante'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Nombre_Solicitante' => $value] : ['name' => $value],
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Correo_Solicitante'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Correo_Solicitante' => $value] : ['email' => $value],
        );
    }

    protected function jobTitle(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Cargo_Solicitante'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Cargo_Solicitante' => $value] : ['job_title' => $value],
        );
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Telefono_Solicitante'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Telefono_Solicitante' => $value] : ['phone' => $value],
        );
    }

    protected function establishmentId(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Id_Dependencia_Solicitada'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Id_Dependencia_Solicitada' => $value] : ['establishment_id' => $value],
        );
    }

    protected function requestedRole(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): mixed {
                if (! $this->usesOriginalSchema()) {
                    return $value;
                }
                $roleId = $attributes['Id_Rol_Solicitado'] ?? null;

                return DB::table('rol')->where('Id_Rol', $roleId)->value('Codigo_Rol');
            },
            set: function (mixed $value): array {
                if (! $this->usesOriginalSchema()) {
                    return ['requested_role' => $value];
                }
                $roleId = DB::table('rol')->where('Codigo_Rol', $value)->value('Id_Rol');

                return $roleId ? ['Id_Rol_Solicitado' => $roleId] : [];
            },
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Contrasena_Hash'] ?? null) : $value,
            set: function (mixed $value): array {
                $hash = is_string($value) && password_get_info($value)['algoName'] !== 'unknown'
                    ? $value
                    : bcrypt((string) $value);

                return $this->usesOriginalSchema() ? ['Contrasena_Hash' => $hash] : ['password' => $hash];
            },
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): mixed {
                if (! $this->usesOriginalSchema()) {
                    return $value;
                }

                return match ($attributes['Estado_Solicitud'] ?? null) {
                    'Pendiente' => 'pending', 'Aprobada' => 'approved', 'Rechazada' => 'rejected', default => strtolower((string) ($attributes['Estado_Solicitud'] ?? 'pending')),
                };
            },
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Estado_Solicitud' => match ($value) {
                'pending' => 'Pendiente', 'approved' => 'Aprobada', 'rejected' => 'Rechazada', default => $value
            }] : ['status' => $value],
        );
    }

    protected function rejectionReason(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Observacion_Resolucion'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Observacion_Resolucion' => $value] : ['rejection_reason' => $value],
        );
    }

    protected function resolvedBy(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Rut_Usuario_Validador'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Rut_Usuario_Validador' => $value] : ['resolved_by' => $value],
        );
    }

    protected function resolvedAt(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): mixed {
                $date = $this->usesOriginalSchema() ? ($attributes['Fecha_Resolucion'] ?? null) : $value;

                return $date ? Carbon::parse($date) : null;
            },
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Fecha_Resolucion' => $value] : ['resolved_at' => $value],
        );
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): mixed {
                $date = $this->usesOriginalSchema() ? ($attributes['Fecha_Solicitud'] ?? null) : $value;

                return $date ? Carbon::parse($date) : null;
            },
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Fecha_Solicitud' => $value] : ['created_at' => $value],
        );
    }

    protected function pendingKey(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->rut : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? [] : ['pending_key' => $value],
        );
    }

    protected $hidden = ['password'];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class, $this->usesOriginalSchema() ? 'Id_Dependencia_Solicitada' : 'establishment_id', 'Id_Dependencia');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, $this->usesOriginalSchema() ? 'Rut_Usuario_Validador' : 'resolved_by', $this->usesOriginalSchema() ? 'Rut_Usuario' : 'id');
    }

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }
}
