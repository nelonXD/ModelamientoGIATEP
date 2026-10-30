<?php

namespace App\Models;

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OccupationalCase extends Model
{
    protected $table = 'occupational_cases';

    protected $guarded = [];

    public function getTable(): string
    {
        return OriginalGiatepSchema::isActive() ? 'caso' : parent::getTable();
    }

    public function getKeyName(): string
    {
        return OriginalGiatepSchema::isActive() ? 'Id_Caso' : parent::getKeyName();
    }

    public function getRouteKeyName(): string
    {
        return OriginalGiatepSchema::isActive() ? 'Folio' : 'folio';
    }

    public function usesTimestamps(): bool
    {
        return $this->usesOriginalSchema() ? false : parent::usesTimestamps();
    }

    protected function usesOriginalSchema(): bool
    {
        return OriginalGiatepSchema::isActive();
    }

    public function newQuery()
    {
        $query = parent::newQuery();

        if ($this->usesOriginalSchema()) {
            $query->with(['worker', 'employer']);
        }

        return $query;
    }

    protected function id(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Id_Caso'] ?? null) : $value);
    }

    protected function folio(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Folio'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Folio' => $value] : ['folio' => $value],
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['Estado'] ?? null) : $value,
            set: fn (mixed $value): array => $this->usesOriginalSchema() ? ['Estado' => $value] : ['status' => $value],
        );
    }

    protected function caseType(): Attribute
    {
        return Attribute::get(function (mixed $value, array $attributes): mixed {
            if (! $this->usesOriginalSchema()) {
                return $value;
            }

            return DB::table('tipo_caso')->where('Id_Clasificacion_Caso', $attributes['Id_Clasificacion_Caso'] ?? null)->value('Nombre_Tipo_Caso');
        });
    }

    protected function workerRut(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => $this->usesOriginalSchema() ? ($attributes['RUT_CI_Trabajador'] ?? null) : $value);
    }

    protected function employerName(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->employer?->Nombre_Empresa : $value);
    }

    protected function employerRut(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->employer?->RUT_Empresa : $value);
    }

    protected function employerAddress(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->employer?->Direccion_Sucursal : $value);
    }

    protected function employerPhone(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->employer?->Telefono : $value);
    }

    protected function employerCommune(): Attribute
    {
        return Attribute::get(function (mixed $value): mixed {
            if (! $this->usesOriginalSchema()) {
                return $value;
            }

            $communeId = $this->employer?->Id_Comuna;

            return $communeId ? DB::table('comuna')->where('Id_Comuna', $communeId)->value('Nombre_Comuna') : null;
        });
    }

    protected function administrator(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->employer?->Organismo_Administrador : $value);
    }

    protected function economicActivity(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->employer?->Actividad_Economica : $value);
    }

    protected function companyType(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->employer?->Tipo_Empresa : $value);
    }

    protected function workerName(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? trim(($this->worker?->Nombre ?? '').' '.($this->worker?->Apellido ?? '')) : $value);
    }

    protected function workerPhone(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->worker?->Telefono_Personal : $value);
    }

    protected function workerCommune(): Attribute
    {
        return Attribute::get(function (mixed $value): mixed {
            if (! $this->usesOriginalSchema()) {
                return $value;
            }

            $communeId = $this->worker?->Id_Comuna;

            return $communeId ? DB::table('comuna')->where('Id_Comuna', $communeId)->value('Nombre_Comuna') : null;
        });
    }

    protected function workerSex(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->worker?->Sexo : $value);
    }

    protected function birthDate(): Attribute
    {
        return Attribute::get(function (mixed $value): mixed {
            $date = $this->usesOriginalSchema() ? $this->worker?->Fecha_Nacimiento : $value;

            return $date ? Carbon::parse($date) : null;
        });
    }

    protected function nationality(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->worker?->Nacionalidad : $value);
    }

    protected function hireDate(): Attribute
    {
        return Attribute::get(function (mixed $value): mixed {
            $date = $this->usesOriginalSchema() ? $this->worker?->Fecha_Ingreso : $value;

            return $date ? Carbon::parse($date) : null;
        });
    }

    protected function contractType(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->worker?->Tipo_Contrato : $value);
    }

    protected function occupationalCategory(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->worker?->Categoria_Ocupacional : $value);
    }

    protected function witnesses(): Attribute
    {
        return Attribute::get(function (mixed $value): mixed {
            if (! $this->usesOriginalSchema()) {
                return $value;
            }

            return DB::table('testigo_caso')->join('testigo', 'testigo_caso.Rut_Testigo', '=', 'testigo.Rut_Testigo')
                ->where('testigo_caso.Id_Caso', $this->getKey())->pluck('Nombre_Testigo')->implode("\n");
        });
    }

    protected function jobTitle(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->worker?->Profesion_U_Oficio : $value);
    }

    protected function workplace(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->establishment?->name : $value);
    }

    protected function eventDate(): Attribute
    {
        return Attribute::get(function (mixed $value): mixed {
            if (! $this->usesOriginalSchema()) {
                return $value ? Carbon::parse($value) : null;
            }

            $date = $this->accident()?->Fecha_Accidente
                ?? DB::table('detalle_enfermedad')->where('Id_Caso', $this->getKey())->value('Fecha_Inicio_Sintomas');

            return $date ? Carbon::parse($date) : null;
        });
    }

    protected function eventTime(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->accident()?->Hora_Accidente : $value);
    }

    protected function eventLocation(): Attribute
    {
        return Attribute::get(fn (mixed $value): mixed => $this->usesOriginalSchema() ? $this->accident()?->Lugar_Accidente : $value);
    }

    protected function narrative(): Attribute
    {
        return Attribute::get(function (mixed $value): mixed {
            if (! $this->usesOriginalSchema()) {
                return $value;
            }

            return DB::table('relato_cuestionario')->where('Id_Caso', $this->getKey())->latest('Id_Relato')->value('Relato');
        });
    }

    protected function details(): Attribute
    {
        return Attribute::get(function (mixed $value): mixed {
            if (! $this->usesOriginalSchema()) {
                return is_string($value) ? (json_decode($value, true) ?: []) : ($value ?? []);
            }

            $accident = $this->accident();
            $disease = DB::table('detalle_enfermedad')->where('Id_Caso', $this->getKey())->first();

            return [
                'affected_body_part' => DB::table('parte_cuerpo_caso')->join('parte_del_cuerpo', 'parte_cuerpo_caso.Id_Parte_Del_Cuerpo', '=', 'parte_del_cuerpo.Id_Parte_Del_Cuerpo')->where('parte_cuerpo_caso.Id_Caso', $this->getKey())->value('Nombre_Parte_Del_Cuerpo') ?? '',
                'accident_severity' => $accident?->Clasificacion_Gravedad ?? '',
                'route_type' => $accident?->Tipo_Trayecto ?? '',
                'activity_before_event' => $accident?->Actividad_Previa_Accidente ?? '',
                'usual_job_description' => $accident?->Trabajo_Habitual ?? '',
                'doing_usual_job' => isset($accident?->Realizaba_Trabajo_Habitual) ? ($accident->Realizaba_Trabajo_Habitual ? 'Sí' : 'No') : '',
                'previous_symptoms' => isset($disease?->Molestias_Previas) ? ($disease->Molestias_Previas ? 'Sí' : 'No') : '',
                'activity_when_symptoms_began' => $disease?->Actividad_Inicio_Sintomas ?? '',
                'symptom_job_position' => $disease?->Puesto_Inicio_Sintomas ?? '',
                'coworkers_same_symptoms' => isset($disease?->Companero_Mismos_Sintomas) ? ($disease->Companero_Mismos_Sintomas ? 'Sí' : 'No') : '',
                'suspected_work_agents' => $disease?->Agentes_Sospechados ?? '',
            ];
        });
    }

    protected $fillable = [
        'folio',
        'created_by',
        'establishment_id',
        'status',
        'case_type',
        'employer_name',
        'employer_rut',
        'employer_address',
        'employer_commune',
        'employer_phone',
        'economic_activity',
        'company_type',
        'administrator',
        'worker_name',
        'worker_rut',
        'worker_address',
        'worker_commune',
        'worker_phone',
        'worker_sex',
        'birth_date',
        'nationality',
        'job_title',
        'workplace',
        'contract_type',
        'occupational_category',
        'hire_date',
        'event_date',
        'event_time',
        'event_location',
        'narrative',
        'witnesses',
        'details',
    ];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class, 'Id_Dependencia', 'Id_Dependencia');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(
            CaseEvidence::class,
            OriginalGiatepSchema::isActive() ? 'Id_Caso' : 'occupational_case_id',
            OriginalGiatepSchema::isActive() ? 'Id_Caso' : 'id',
        );
    }

    public function investigation(): HasOne
    {
        if ($this->usesOriginalSchema()) {
            return $this->newHasOne(Investigation::query(), $this, 'Id_Caso', 'Id_Caso');
        }

        return $this->hasOne(Investigation::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'RUT_CI_Trabajador', 'RUT_CI_Trabajador');
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class, 'Id_Empresa', 'Id_Empresa');
    }

    private function accident(): ?object
    {
        return DB::table('detalle_accidente')->where('Id_Caso', $this->getKey())->first();
    }

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'hire_date' => 'date', 'event_date' => 'date', 'details' => 'array'];
    }
}
