<?php

namespace App\Models;

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Eloquent\Model;

class CaseEvidence extends Model
{
    protected $table = 'case_evidences';

    protected $guarded = [];

    public function getTable(): string
    {
        return OriginalGiatepSchema::isActive() ? 'evidencia' : parent::getTable();
    }

    public function getKeyName(): string
    {
        return OriginalGiatepSchema::isActive() ? 'Id_Evidencia' : parent::getKeyName();
    }

    public function usesTimestamps(): bool
    {
        return $this->originalSchema() ? false : parent::usesTimestamps();
    }

    public function getIncrementing(): bool
    {
        return true;
    }

    protected function originalSchema(): bool
    {
        return OriginalGiatepSchema::isActive();
    }

    public function getAttribute($key)
    {
        if (! $this->originalSchema()) {
            return parent::getAttribute($key);
        }

        return match ($key) {
            'id' => $this->attributes['Id_Evidencia'] ?? null,
            'occupational_case_id' => $this->attributes['Id_Caso'] ?? null,
            'original_name' => $this->attributes['Nombre_Archivo'] ?? null,
            'mime_type' => $this->attributes['Tipo_MIME'] ?? null,
            'size' => isset($this->attributes['Archivo']) ? strlen($this->attributes['Archivo']) : 0,
            'path' => 'database',
            default => parent::getAttribute($key),
        };
    }

    public function setAttribute($key, $value)
    {
        if (! $this->originalSchema()) {
            return parent::setAttribute($key, $value);
        }

        $column = match ($key) {
            'occupational_case_id' => 'Id_Caso',
            'original_name' => 'Nombre_Archivo',
            'mime_type' => 'Tipo_MIME',
            'blob' => 'Archivo',
            default => $key,
        };

        return parent::setAttribute($column, $value);
    }

    public function contents(): ?string
    {
        return $this->originalSchema() ? ($this->attributes['Archivo'] ?? null) : null;
    }
}
