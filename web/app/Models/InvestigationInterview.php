<?php

namespace App\Models;

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvestigationInterview extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return OriginalGiatepSchema::isActive() ? 'declaracion' : parent::getTable();
    }

    public function getKeyName(): string
    {
        return OriginalGiatepSchema::isActive() ? 'Id_Declaracion' : parent::getKeyName();
    }

    public function usesTimestamps(): bool
    {
        return OriginalGiatepSchema::isActive() ? false : parent::usesTimestamps();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InterviewAnswer::class, OriginalGiatepSchema::isActive() ? 'Id_Declaracion' : 'investigation_interview_id');
    }

    protected function questionnaireFormId(): Attribute
    {
        return Attribute::make(get: fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive() ? ($attributes['Id_Formulario'] ?? null) : $value);
    }

    protected function questionnaireType(): Attribute
    {
        return Attribute::get(fn (): string => 'adapted');
    }

    protected function witnessType(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive()
                ? DB::table('tipo_entrevistado')->where('Id_Tipo_Entrevistado', $attributes['Id_Tipo_Entrevistado'] ?? null)->value('Nombre_Tipo')
                : $value,
            set: fn (mixed $value): array => OriginalGiatepSchema::isActive() ? [] : ['witness_type' => $value],
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive()
                ? $this->intervieweeNameFromOriginalSchema($attributes)
                : $value,
            set: fn (mixed $value): array => OriginalGiatepSchema::isActive() ? [] : ['interviewee_name' => $value],
        );
    }

    protected function intervieweeNameFromOriginalSchema(array $attributes): mixed
    {
        $rut = DB::table('testigo_caso')->where('Id_Testigo_Caso', $attributes['Id_Testigo_Caso'] ?? null)->value('Rut_Testigo');

        return $rut ? DB::table('testigo')->where('Rut_Testigo', $rut)->value('Nombre_Testigo') : null;
    }

    protected function intervieweeRole(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive() ? ($attributes['Cargo_Al_Declarar'] ?? null) : $value);
    }

    protected function status(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive() ? ($attributes['Estado'] ?? null) : $value);
    }

    protected function interviewDate(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes): mixed => OriginalGiatepSchema::isActive()
            ? (isset($attributes['Fecha_Entrevista']) ? Carbon::parse($attributes['Fecha_Entrevista']) : null)
            : $value);
    }

    protected function casts(): array
    {
        return ['interview_date' => 'date'];
    }
}
