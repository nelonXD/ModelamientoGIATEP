<?php

namespace App\Support;

use App\Models\Establishment;
use App\Models\OccupationalCase;
use Illuminate\Support\Collection;

class DemoWorkspace
{
    public const SESSION_KEY = 'giatep_demo_changes';

    /** @return array<string, mixed> */
    public function data(): array
    {
        if (OriginalGiatepSchema::isActive()) {
            $storedCases = OccupationalCase::query()->with(['establishment', 'evidences', 'investigation.owner'])->orderByDesc('Id_Caso')->get();
            $establishments = Establishment::query()->orderBy('Nombre_Dependencia')->pluck('Nombre_Dependencia')->all();
            $cases = $storedCases->map(fn (OccupationalCase $case): array => [
                'id' => $case->folio, 'type' => $case->case_type, 'worker' => $case->worker_name, 'rut' => $case->worker_rut,
                'job_title' => $case->job_title ?? 'Funcionario/a de salud', 'phone' => $case->worker_phone ?? '',
                'establishment' => $case->establishment?->name ?? '', 'workplace' => $case->workplace ?? '',
                'employer_name' => $case->employer_name ?? '', 'employer_rut' => $case->employer_rut ?? '',
                'employer_address' => $case->employer_address ?? '', 'administrator' => $case->administrator ?? '',
                'economic_activity' => $case->economic_activity ?? '', 'date' => $case->event_date?->toDateString() ?? '',
                'time' => $case->event_time ? substr((string) $case->event_time, 0, 5) : '', 'location' => $case->event_location ?? '',
                'status' => $case->status, 'qualification' => 'Pendiente',
                'injury' => $case->details['affected_body_part'] ?? 'Por confirmar', 'body_part' => $case->details['affected_body_part'] ?? 'Por confirmar',
                'story' => $case->narrative ?? '', 'witnesses' => '', 'investigation_id' => $case->investigation?->folio, 'evidence_count' => $case->evidences->count(),
                'form_type' => str_contains(strtolower($case->case_type ?? ''), 'enfermedad') ? 'DIEP' : 'DIAT',
            ]);
            $investigations = $storedCases->filter(fn (OccupationalCase $case): bool => $case->investigation !== null)
                ->map(function (OccupationalCase $case): array {
                    $investigation = $case->investigation;

                    return [
                        'id' => $investigation->folio,
                        'case_id' => $case->folio,
                        'case_type' => $case->case_type,
                        'worker' => $case->worker_name,
                        'establishment' => $case->establishment?->name ?? 'Establecimiento de salud',
                        'status' => match ($investigation->status) {
                            'En investigación', 'En desarrollo', 'En curso' => 'En proceso',
                            'Concluida', 'Cerrado' => 'Realizada',
                            default => $investigation->status,
                        },
                        'progress' => match ($investigation->status) {
                            'Concluida', 'Cerrado' => 100,
                            'Con observaciones' => 85,
                            'En desarrollo' => 65,
                            default => 30,
                        },
                        'owner' => $investigation->owner?->name ?? 'Responsable técnico',
                        'updated' => $investigation->updated_at?->toDateString() ?? now()->toDateString(),
                        'kind' => 'Árbol de causas',
                        'final_story' => $investigation->final_story ?: $case->narrative,
                        'has_facts' => false,
                        'pending' => 'Recopilar entrevistas y evidencias',
                    ];
                })->values();

            return [
                'cases' => $cases, 'investigations' => $investigations, 'measures' => collect(), 'observations' => collect(),
                'establishments' => $establishments, 'activity' => [],
            ];
        }

        $storedCases = OccupationalCase::query()
            ->with(['establishment', 'investigation.owner', 'investigation.measures', 'investigation.facts', 'investigation.causalNodes', 'evidences'])
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
            'evidence_count' => $case->evidences->count(),
            'form_type' => $case->case_type === 'Enfermedad profesional' ? 'DIEP' : 'DIAT',
        ]);

        $investigations = $storedCases->pluck('investigation')->filter()->map(fn ($investigation): array => [
            'id' => $investigation->folio,
            'case_id' => $investigation->occupationalCase->folio,
            'case_type' => $investigation->occupationalCase->case_type,
            'worker' => $investigation->occupationalCase->worker_name,
            'establishment' => $investigation->occupationalCase->establishment?->name ?? 'Establecimiento de salud',
            'status' => match ($investigation->status) {
                'En investigación', 'En desarrollo', 'En curso' => 'En proceso',
                'Concluida', 'Cerrado' => 'Realizada',
                default => $investigation->status,
            },
            'progress' => match ($investigation->status) {
                'Concluida', 'Cerrado' => 100,
                'Con observaciones' => 85,
                'En desarrollo' => 65,
                default => 30,
            },
            'owner' => $investigation->owner?->name ?? 'Responsable técnico',
            'updated' => $investigation->updated_at->toDateString(),
            'kind' => 'Árbol de causas',
            'final_story' => $investigation->final_story,
            'has_facts' => $investigation->facts->isNotEmpty() || $investigation->causalNodes->isNotEmpty(),
            'pending' => match ($investigation->status) {
                'Concluida', 'Cerrado' => 'Sin pendientes (Expediente regularizado)',
                'Con observaciones' => 'Responder observaciones de Jefatura / Alta Dirección',
                'En desarrollo' => 'Completar árbol de causas y validar medidas',
                default => 'Recopilar entrevistas y evidencias',
            },
        ])->values();

        $measures = $storedCases->pluck('investigation')->filter()->flatMap(fn ($investigation) => $investigation->measures->map(fn ($measure): array => [
            'id' => 'MED-'.str_pad((string) $measure->id, 3, '0', STR_PAD_LEFT),
            'case_id' => $investigation->occupationalCase->folio,
            'investigation_id' => $investigation->folio,
            'cause' => 'Causa técnica asociada a '.$investigation->folio,
            'description' => $measure->description,
            'category' => $measure->category ?? 'Administrativa',
            'owner' => $measure->responsible,
            'due' => $measure->due_date?->toDateString() ?? 'Sin plazo fijado',
            'status' => $measure->status,
            'progress' => $measure->progress,
        ]))->values();

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
    public function metrics(): array
    {
        $data = $this->data();

        return [
            'cases' => $data['cases']->count(),
            'investigations_pending' => $data['investigations']->whereNotIn('status', ['Concluida', 'Cerrado'])->count(),
            'with_observations' => $data['investigations']->where('status', 'Con observaciones')->count(),
            'measures_due_soon' => $data['measures']->whereIn('status', ['Pendiente', 'En ejecución'])->count(),
            'measures_overdue' => $data['measures']->where('status', 'Vencida')->count(),
        ];
    }

    /** @return array<string, mixed>|null */
    public function find(string $collection, string $id): ?array
    {
        $items = $this->data()[$collection] ?? collect();

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
