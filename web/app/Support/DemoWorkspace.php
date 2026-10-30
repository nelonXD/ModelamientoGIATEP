<?php

namespace App\Support;

use App\Models\Establishment;
use App\Models\OccupationalCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DemoWorkspace
{
    public const SESSION_KEY = 'giatep_demo_changes';

    /** @return array<string, mixed> */
    public function data(bool $includeDetails = false, bool $includeMeasures = true): array
    {
        if (OriginalGiatepSchema::isActive()) {
            $relations = ['establishment', 'investigation.owner'];
            if ($includeDetails) {
                $relations[] = 'investigation.facts';
            }

            $storedCases = OccupationalCase::query()->with($relations)->withCount('evidences')->orderByDesc('Id_Caso')->get();
            $establishments = Establishment::query()->orderBy('Nombre_Dependencia')->pluck('Nombre_Dependencia')->all();
            $caseTypes = DB::table('tipo_caso')->pluck('Nombre_Tipo_Caso', 'Id_Clasificacion_Caso');
            $accidents = DB::table('detalle_accidente')->get()->keyBy('Id_Caso');
            $diseases = DB::table('detalle_enfermedad')->get()->keyBy('Id_Caso');
            $bodyParts = DB::table('parte_cuerpo_caso')
                ->join('parte_del_cuerpo', 'parte_cuerpo_caso.Id_Parte_Del_Cuerpo', '=', 'parte_del_cuerpo.Id_Parte_Del_Cuerpo')
                ->get(['parte_cuerpo_caso.Id_Caso', 'parte_del_cuerpo.Nombre_Parte_Del_Cuerpo'])
                ->keyBy('Id_Caso');
            $narrativeIds = DB::table('relato_cuestionario')
                ->select('Id_Caso', DB::raw('MAX(Id_Relato) as Id_Relato'))
                ->groupBy('Id_Caso');
            $narratives = DB::table('relato_cuestionario as relatos')
                ->joinSub($narrativeIds, 'ultimo_relato', fn ($join) => $join->on('relatos.Id_Relato', '=', 'ultimo_relato.Id_Relato'))
                ->pluck('relatos.Relato', 'relatos.Id_Caso');

            $cases = $storedCases->map(function (OccupationalCase $case) use ($caseTypes, $accidents, $diseases, $bodyParts, $narratives): array {
                $caseId = $case->getKey();
                $type = $caseTypes[$case->getAttributes()['Id_Clasificacion_Caso'] ?? null] ?? null;
                $accident = $accidents->get($caseId);
                $disease = $diseases->get($caseId);
                $eventDate = $accident?->Fecha_Accidente ?? $disease?->Fecha_Inicio_Sintomas;
                $bodyPart = $bodyParts->get($caseId)?->Nombre_Parte_Del_Cuerpo ?? 'Por confirmar';

                return [
                    'id' => $case->folio, 'type' => $type, 'worker' => $case->worker_name, 'rut' => $case->worker_rut,
                    'job_title' => $case->job_title ?? 'Funcionario/a de salud', 'phone' => $case->worker_phone ?? '',
                    'establishment' => $case->establishment?->name ?? '', 'workplace' => $case->workplace ?? '',
                    'employer_name' => $case->employer_name ?? '', 'employer_rut' => $case->employer_rut ?? '',
                    'employer_address' => $case->employer_address ?? '', 'administrator' => $case->administrator ?? '',
                    'economic_activity' => $case->economic_activity ?? '', 'date' => $eventDate ? substr((string) $eventDate, 0, 10) : '',
                    'time' => $accident?->Hora_Accidente ? substr((string) $accident->Hora_Accidente, 0, 5) : '', 'location' => $accident?->Lugar_Accidente ?? '',
                    'status' => $case->status, 'qualification' => 'Pendiente',
                    'injury' => $bodyPart, 'body_part' => $bodyPart,
                    'story' => $narratives[$caseId] ?? '', 'witnesses' => '', 'investigation_id' => $case->investigation?->folio, 'evidence_count' => $case->evidences_count,
                    'form_type' => str_contains(strtolower($type ?? ''), 'enfermedad') ? 'DIEP' : 'DIAT',
                ];
            });
            $investigations = $storedCases->filter(fn (OccupationalCase $case): bool => $case->investigation !== null)
                ->map(function (OccupationalCase $case) use ($caseTypes, $narratives, $includeDetails): array {
                    $investigation = $case->investigation;
                    $caseType = $caseTypes[$case->getAttributes()['Id_Clasificacion_Caso'] ?? null] ?? null;

                    return [
                        'id' => $investigation->folio,
                        'case_id' => $case->folio,
                        'case_type' => $caseType,
                        'worker' => $case->worker_name,
                        'establishment' => $case->establishment?->name ?? 'Establecimiento de salud',
                        'status' => match ($investigation->status) {
                            'En investigación', 'Borrador' => 'En desarrollo',
                            'En curso' => 'En desarrollo',
                            'Concluida' => 'Completada',
                            'Cerrado' => 'Cerrado',
                            default => $investigation->status,
                        },
                        'progress' => match ($investigation->status) {
                            'Concluida', 'Cerrado', 'Completada' => 100,
                            'Con observaciones' => 85,
                            'En desarrollo' => 65,
                            default => 30,
                        },
                        'owner' => $investigation->owner?->name ?? 'Responsable técnico',
                        'updated' => $investigation->updated_at?->toDateString() ?? now()->toDateString(),
                        'kind' => 'Árbol de causas',
                        'final_story' => $investigation->final_story ?: ($narratives[$case->getKey()] ?? ''),
                        'has_facts' => $includeDetails && $investigation->facts->isNotEmpty(),
                        'pending' => $includeDetails && $investigation->facts->isNotEmpty() ? 'Revisar los hechos, el árbol y las medidas guardadas' : 'Recopilar entrevistas y evidencias',
                    ];
                })->values();

            return [
                'cases' => $cases, 'investigations' => $investigations, 'measures' => collect(), 'observations' => collect(),
                'establishments' => $establishments, 'activity' => [],
            ];
        }

        $relations = ['establishment', 'investigation.owner'];
        if ($includeMeasures) {
            $relations[] = 'investigation.measures';
        }
        if ($includeDetails) {
            $relations = [...$relations, 'investigation.facts', 'investigation.causalNodes'];
        }

        $storedCases = OccupationalCase::query()
            ->with($relations)
            ->withCount('evidences')
            ->orderByDesc('event_date')
            ->get();

        $establishments = Establishment::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->all();

        if (empty($establishments)) {
            $establishments = [
                'CESFAM Nororiente',
                'CESFAM Los Aromos',
                'CESFAM Nuevo Horizonte',
                'CESFAM 2 de Septiembre',
                'SAR Central',
                'Dirección Comunal de Salud',
            ];
        }

        if ($storedCases->isEmpty()) {
            return [
                'cases' => collect(),
                'investigations' => collect(),
                'measures' => collect(),
                'observations' => collect(),
                'establishments' => $establishments,
                'activity' => [],
            ];
        }

        $cases = $storedCases->map(fn (OccupationalCase $case): array => [
            'id' => $case->folio,
            'type' => $case->case_type,
            'worker' => $case->worker_name,
            'rut' => $case->worker_rut,
            'job_title' => $case->job_title ?? 'Funcionario/a de salud',
            'phone' => $case->worker_phone ?? '',
            'establishment' => $case->establishment?->name ?? $case->workplace,
            'workplace' => $case->workplace ?? $case->establishment?->name,
            'employer_name' => $case->employer_name ?? 'Dirección Comunal de Salud de Los Ángeles',
            'employer_rut' => $case->employer_rut ?? '69.170.102-6',
            'employer_address' => $case->employer_address ?? 'Calle Valdivia #150, Los Ángeles',
            'administrator' => $case->administrator ?? 'ACHS',
            'economic_activity' => $case->economic_activity ?? 'Servicios de Salud Pública Municipal (APS)',
            'date' => $case->event_date->toDateString(),
            'time' => $case->event_time ? substr((string) $case->event_time, 0, 5) : '09:00',
            'location' => $case->event_location ?? 'Dependencias del establecimiento',
            'status' => $case->status,
            'qualification' => $case->status === 'Cerrado' ? 'Laboral (Cerrado)' : ($case->case_type === 'Enfermedad profesional' ? 'En estudio ACHS' : 'Pendiente'),
            'injury' => $case->details['affected_body_part'] ?? $case->details['injury_type'] ?? 'Por confirmar',
            'body_part' => $case->details['affected_body_part'] ?? 'Por confirmar',
            'story' => $case->narrative,
            'witnesses' => $case->witnesses ?? 'Sin testigos registrados.',
            'investigation_id' => $case->investigation?->folio,
            'evidence_count' => $case->evidences_count,
            'form_type' => $case->case_type === 'Enfermedad profesional' ? 'DIEP' : 'DIAT',
        ]);

        $investigations = $storedCases->filter(fn (OccupationalCase $case): bool => $case->investigation !== null)
            ->map(function (OccupationalCase $case) use ($includeDetails): array {
                $investigation = $case->investigation;

                return [
                    'id' => $investigation->folio,
                    'case_id' => $case->folio,
                    'case_type' => $case->case_type,
                    'worker' => $case->worker_name,
                    'establishment' => $case->establishment?->name ?? 'Establecimiento de salud',
                    'status' => match ($investigation->status) {
                        'En investigación', 'Borrador', 'En curso' => 'En desarrollo',
                        'Concluida' => 'Completada',
                        'Cerrado' => 'Cerrado',
                        default => $investigation->status,
                    },
                    'progress' => match ($investigation->status) {
                        'Concluida', 'Cerrado', 'Completada' => 100,
                        'Con observaciones' => 85,
                        'En desarrollo' => 65,
                        default => 30,
                    },
                    'owner' => $investigation->owner?->name ?? 'Responsable técnico',
                    'updated' => $investigation->updated_at->toDateString(),
                    'kind' => 'Árbol de causas',
                    'final_story' => $investigation->final_story,
                    'has_facts' => $includeDetails && ($investigation->facts->isNotEmpty() || $investigation->causalNodes->isNotEmpty()),
                    'pending' => match ($investigation->status) {
                        'Concluida', 'Cerrado', 'Completada' => 'Sin pendientes (Expediente regularizado)',
                        'Con observaciones' => 'Responder observaciones de Jefatura / Alta Dirección',
                        'En desarrollo' => 'Completar árbol de causas y validar medidas',
                        default => 'Recopilar entrevistas y evidencias',
                    },
                ];
            })->values();

        $measures = $includeMeasures ? $storedCases
            ->filter(fn (OccupationalCase $case): bool => $case->investigation !== null)
            ->flatMap(fn (OccupationalCase $case) => $case->investigation->measures->map(fn ($measure): array => [
                'id' => 'MED-'.str_pad((string) $measure->id, 3, '0', STR_PAD_LEFT),
                'case_id' => $case->folio,
                'investigation_id' => $case->investigation->folio,
                'cause' => 'Causa técnica asociada a '.$case->investigation->folio,
                'description' => $measure->description,
                'category' => $measure->category ?? 'Administrativa',
                'owner' => $measure->responsible,
                'due' => $measure->due_date?->toDateString() ?? 'Sin plazo fijado',
                'status' => $measure->status,
                'progress' => $measure->progress,
            ]))->values() : collect();

        $observations = collect();

        $activity = [];
        foreach ($storedCases->take(5) as $storedCase) {
            $activity[] = [
                'time' => $storedCase->created_at?->diffForHumans() ?? $storedCase->event_date->toDateString(),
                'text' => "Caso {$storedCase->folio} ({$storedCase->case_type}) registrado para {$storedCase->worker_name}.",
            ];
            if ($storedCase->investigation) {
                $activity[] = [
                    'time' => $storedCase->investigation->updated_at?->diffForHumans() ?? $storedCase->event_date->toDateString(),
                    'text' => "Investigación {$storedCase->investigation->folio} en estado: {$storedCase->investigation->status}.",
                ];
            }
        }

        return compact('cases', 'investigations', 'measures', 'observations', 'establishments', 'activity');
    }

    /** @return array<string, int> */
    public function metrics(?array $data = null): array
    {
        $data ??= $this->data();

        return [
            'cases' => $data['cases']->count(),
            'investigations_pending' => $data['investigations']->whereNotIn('status', ['Concluida', 'Cerrado'])->count(),
            'with_observations' => $data['investigations']->where('status', 'Con observaciones')->count(),
            'measures_due_soon' => $data['measures']->whereIn('status', ['Pendiente', 'En ejecución'])->count(),
            'measures_overdue' => $data['measures']->where('status', 'Vencida')->count(),
        ];
    }

    /** @return array<string, mixed>|null */
    public function find(string $collection, string $id, ?array $data = null): ?array
    {
        $data ??= $this->data();
        $items = $data[$collection] ?? collect();

        $found = $items instanceof Collection ? $items->firstWhere('id', $id) : null;
        if ($found) {
            return $found;
        }

        return null;
    }

    /** @return array<string, string> */
    public function permissions(): array
    {
        return [
            'cases' => 'cases.read', 'investigations' => 'investigations.read', 'reviews' => 'reviews.read',
            'observations' => 'observations.read', 'measures' => 'measures.read', 'statistics' => 'statistics.read',
            'reports' => 'reports.export', 'profile' => 'profile.read', 'users' => 'users.manage',
            'roles' => 'roles.manage', 'establishments' => 'establishments.manage', 'settings' => 'settings.manage',
        ];
    }
}
