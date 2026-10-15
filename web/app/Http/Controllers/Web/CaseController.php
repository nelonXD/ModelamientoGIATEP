<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOccupationalCaseRequest;
use App\Models\Establishment;
use App\Models\OccupationalCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CaseController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPermission('cases.read'), 403);

        $cases = OccupationalCase::query()->with(['evidences', 'establishment', 'investigation'])->latest()->get()->map(fn (OccupationalCase $case): array => [
            'folio' => $case->folio, 'type' => $case->case_type, 'person' => $case->worker_name,
            'workerRut' => $case->worker_rut, 'jobTitle' => $case->job_title, 'workplace' => $case->workplace,
            'phone' => $case->worker_phone, 'employerName' => $case->employer_name, 'employerRut' => $case->employer_rut,
            'administrator' => $case->administrator, 'establishment' => $case->establishment?->name ?? $case->workplace,
            'employerAddress' => $case->employer_address, 'date' => $case->event_date->format('d-m-Y'),
            'time' => $case->event_time ? substr((string) $case->event_time, 0, 5) : '', 'location' => $case->event_location,
            'status' => $case->status, 'summary' => $case->narrative, 'witnesses' => $case->witnesses,
            'evidenceFiles' => $case->evidences->map(fn ($file): array => ['name' => $file->original_name, 'type' => $file->mime_type, 'size' => $file->size])->all(),
            'has_investigation' => $case->investigation !== null || in_array($case->status, ['En investigación', 'Con observaciones', 'Seguimiento de medidas', 'Cerrado']),
            'investigation_id' => $case->investigation?->folio,
            'requires_investigation' => !in_array($case->case_type, ['Accidente o enfermedad común', 'Accidente común', 'Enfermedad común']),
        ]);

        return view('modules.cases.index', compact('cases'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->hasPermission('cases.create'), 403);

        return view('modules.cases.create', ['establishments' => Establishment::query()->where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(StoreOccupationalCaseRequest $request): RedirectResponse
    {
        $case = DB::transaction(function () use ($request): OccupationalCase {
            $data = $request->validated();
            $establishment = Establishment::query()->firstOrCreate(
                ['name' => $data['establishment']],
                ['type' => 'Centro de trabajo', 'is_active' => true],
            );

            $case = OccupationalCase::create([
                ...collect($data)->except(['establishment', 'evidence_files', 'phone'])->all(),
                'folio' => $this->nextFolio(), 'created_by' => $request->user()->id,
                'establishment_id' => $establishment->id, 'worker_phone' => $data['phone'] ?? null,
                'details' => collect($request->all())->only(['company_tenure', 'income_type', 'indigenous_people', 'work_schedule', 'activity_before_event', 'usual_job_description', 'doing_usual_job', 'accident_severity', 'accident_scope', 'route_type', 'proof_types', 'proof_detail', 'affected_body_part', 'symptom_duration', 'previous_symptoms', 'activity_when_symptoms_began', 'symptom_job_position', 'coworkers_same_symptoms', 'suspected_work_agents', 'exposure_duration'])->all(),
            ]);

            foreach ($request->file('evidence_files', []) as $file) {
                $path = $file->store("case-evidences/{$case->id}", 'local');
                $case->evidences()->create(['uploaded_by' => $request->user()->id, 'original_name' => $file->getClientOriginalName(), 'path' => $path, 'mime_type' => $file->getMimeType(), 'size' => $file->getSize()]);
            }

            return $case;
        });

        return to_route('modules.cases.index')->with('demo_status', "Caso {$case->folio} guardado correctamente. Ya puedes revisarlo en la lista antes de iniciar una investigación.");
    }

    private function nextFolio(): string
    {
        $prefix = 'GIATEP-'.now()->year.'-';
        $last = OccupationalCase::query()->where('folio', 'like', $prefix.'%')->lockForUpdate()->max('folio');
        $sequence = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
