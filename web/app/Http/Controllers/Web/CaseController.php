<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseEvidenceRequest;
use App\Http\Requests\StoreOccupationalCaseRequest;
use App\Http\Requests\UpdateOccupationalCaseRequest;
use App\Models\CaseEvidence;
use App\Models\CatalogOption;
use App\Models\Employer;
use App\Models\Establishment;
use App\Models\OccupationalCase;
use App\Models\Worker;
use App\Support\OriginalGiatepSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CaseController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPermission('cases.read'), 403);

        $relations = OriginalGiatepSchema::isActive() ? ['evidences', 'establishment'] : ['evidences', 'establishment', 'investigation'];
        $cases = OccupationalCase::query()->with($relations)->when(OriginalGiatepSchema::isActive(), fn ($query) => $query->orderByDesc('Id_Caso'), fn ($query) => $query->latest())->get()->map(fn (OccupationalCase $case): array => [
            'folio' => $case->folio, 'type' => $case->case_type, 'person' => $case->worker_name,
            'workerRut' => $case->worker_rut, 'jobTitle' => $case->job_title, 'workplace' => $case->workplace,
            'phone' => $case->worker_phone, 'employerName' => $case->employer_name, 'employerRut' => $case->employer_rut,
            'administrator' => $case->administrator, 'establishment' => $case->establishment?->name ?? $case->workplace,
            'employerAddress' => $case->employer_address, 'date' => $case->event_date->format('d-m-Y'),
            'time' => $case->event_time ? substr((string) $case->event_time, 0, 5) : '', 'location' => $case->event_location,
            'status' => ! OriginalGiatepSchema::isActive() && $case->investigation && in_array($case->status, ['Borrador', 'Registrado'], true)
                ? 'En investigación'
                : $case->status,
            'summary' => $case->narrative, 'witnesses' => $case->witnesses,
            'evidenceFiles' => $case->evidences->map(fn ($file): array => ['name' => $file->original_name, 'type' => $file->mime_type, 'size' => $file->size])->all(),
            'has_investigation' => ! OriginalGiatepSchema::isActive() && $case->investigation !== null || in_array($case->status, ['En investigación', 'Con observaciones', 'Seguimiento de medidas', 'Cerrado']),
            'investigation_id' => OriginalGiatepSchema::isActive() ? null : $case->investigation?->folio,
            'requires_investigation' => ! in_array($case->case_type, ['Accidente o enfermedad común', 'Accidente común', 'Enfermedad común']),
        ]);

        $caseTypes = $this->catalogs()->get('case_type', collect());
        $caseStatuses = ['Borrador', 'Registrado', 'En investigación', 'Cerrado'];

        return view('modules.cases.index', compact('cases', 'caseTypes', 'caseStatuses'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->hasPermission('cases.create'), 403);

        $catalogs = $this->catalogs();

        return view('modules.cases.create', [
            'establishments' => Establishment::query()->orderBy('Nombre_Dependencia')->get(),
            'catalogs' => $catalogs,
        ]);
    }

    public function store(StoreOccupationalCaseRequest $request): RedirectResponse
    {
        if (OriginalGiatepSchema::isActive()) {
            $case = $this->storeOriginalCase($request);

            return to_route('modules.cases.index')->with('demo_status', "Caso {$case->folio} guardado correctamente en las tablas relacionadas del esquema GIATEP.");
        }

        $case = DB::transaction(function () use ($request): OccupationalCase {
            $data = $request->validated();
            $establishment = Establishment::query()->whereKey($data['establishment_id'])->where('is_active', true)->firstOrFail();

            $detailKeys = [
                'company_tenure',
                'income_type',
                'indigenous_people',
                'work_schedule',
                'activity_before_event',
                'usual_job_description',
                'doing_usual_job',
                'accident_severity',
                'accident_scope',
                'route_type',
                'proof_types',
                'proof_detail',
                'affected_body_part',
                'symptom_duration',
                'previous_symptoms',
                'activity_when_symptoms_began',
                'symptom_job_position',
                'coworkers_same_symptoms',
                'suspected_work_agents',
                'exposure_duration',
                'work_start_time',
                'work_end_time',
            ];

            $case = OccupationalCase::create([
                ...collect($data)->except([
                    'establishment_id',
                    'evidence_files',
                    'phone',
                    ...$detailKeys,
                ])->all(),
                'folio' => $this->nextFolio(),
                'created_by' => $request->user()->id,
                'establishment_id' => $establishment->id,
                'worker_phone' => $data['phone'] ?? null,
                'details' => collect($request->all())->only($detailKeys)->all(),
            ]);

            foreach ($request->file('evidence_files', []) as $file) {
                $path = $file->store("case-evidences/{$case->id}", 'local');
                $case->evidences()->create(['uploaded_by' => $request->user()->id, 'original_name' => $file->getClientOriginalName(), 'path' => $path, 'mime_type' => $file->getMimeType(), 'size' => $file->getSize()]);
            }

            return $case;
        });

        return to_route('modules.cases.index')->with('demo_status', "Caso {$case->folio} guardado correctamente. Ya puedes revisarlo en la lista antes de iniciar una investigación.");
    }

    public function edit(Request $request, OccupationalCase $case): View
    {
        abort_unless($request->user()->hasPermission('cases.create'), 403);

        return view('modules.cases.edit', [
            'case' => $case,
            'establishments' => Establishment::query()->orderBy('Nombre_Dependencia')->get(),
            'catalogs' => $this->catalogs(),
        ]);
    }

    public function update(UpdateOccupationalCaseRequest $request, OccupationalCase $case): RedirectResponse
    {
        $data = $request->validated();

        if (OriginalGiatepSchema::isActive()) {
            $this->updateOriginalCase($data, $case);

            return to_route('modules.cases.index')->with('demo_status', "Caso {$case->folio} actualizado correctamente en las tablas relacionadas del esquema GIATEP.");
        }

        $establishment = Establishment::query()->whereKey($data['establishment_id'])->where('is_active', true)->firstOrFail();

        $case->update([
            ...collect($data)->except(['establishment_id', 'evidence_files', 'phone'])->all(),
            'establishment_id' => $establishment->id,
            'worker_phone' => $data['phone'] ?? null,
        ]);

        return to_route('modules.cases.index')->with('demo_status', "Caso {$case->folio} actualizado correctamente.");
    }

    public function evidence(Request $request, OccupationalCase $case): View
    {
        abort_unless($request->user()->hasPermission('cases.read'), 403);

        return view('modules.cases.evidence', ['case' => $case->load('evidences')]);
    }

    public function storeEvidence(StoreCaseEvidenceRequest $request, OccupationalCase $case): RedirectResponse
    {
        if (OriginalGiatepSchema::isActive()) {
            foreach ($request->file('evidence_files') as $file) {
                DB::table('evidencia')->insert([
                    'Id_Caso' => $case->getKey(), 'Nombre_Archivo' => $file->getClientOriginalName(),
                    'Tipo_MIME' => $file->getMimeType(), 'Archivo' => file_get_contents($file->getRealPath()),
                    'Fecha_Carga' => now(),
                ]);
            }

            return to_route('modules.cases.evidence.index', ['case' => $case->folio])->with('demo_status', 'Evidencia agregada correctamente.');
        }

        foreach ($request->file('evidence_files') as $file) {
            $path = $file->store("case-evidences/{$case->id}", 'local');
            $case->evidences()->create([
                'uploaded_by' => $request->user()->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return to_route('modules.cases.evidence.index', ['case' => $case->folio])->with('demo_status', 'Evidencia agregada correctamente.');
    }

    public function downloadEvidence(Request $request, OccupationalCase $case, CaseEvidence $evidence): StreamedResponse
    {
        abort_unless($request->user()->hasPermission('cases.read'), 403);
        abort_unless($evidence->occupational_case_id === $case->id, 404);

        if (OriginalGiatepSchema::isActive()) {
            $contents = $evidence->contents();
            abort_if($contents === null, 404);

            return response()->streamDownload(fn () => print ($contents), $evidence->original_name, [
                'Content-Type' => $evidence->mime_type ?: 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        abort_unless(Storage::disk('local')->exists($evidence->path), 404);

        return Storage::disk('local')->download($evidence->path, $evidence->original_name);
    }

    public function previewEvidence(Request $request, OccupationalCase $case, CaseEvidence $evidence): StreamedResponse
    {
        abort_unless($request->user()->hasPermission('cases.read'), 403);
        abort_unless($evidence->occupational_case_id === $case->id, 404);
        abort_unless(in_array($evidence->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true), 415);

        if (OriginalGiatepSchema::isActive()) {
            $contents = $evidence->contents();
            abort_if($contents === null, 404);

            return response()->stream(fn () => print ($contents), 200, [
                'Content-Type' => $evidence->mime_type,
                'Content-Disposition' => 'inline; filename="'.addslashes($evidence->original_name).'"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        abort_unless(Storage::disk('local')->exists($evidence->path), 404);

        return Storage::disk('local')->response($evidence->path, $evidence->original_name, [
            'Content-Type' => $evidence->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function deleteEvidence(Request $request, OccupationalCase $case, CaseEvidence $evidence): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('cases.create'), 403);
        abort_unless($evidence->occupational_case_id === $case->id, 404);

        if (OriginalGiatepSchema::isActive()) {
            $evidence->delete();

            return to_route('modules.cases.evidence.index', ['case' => $case->folio])
                ->with('demo_status', 'La evidencia se eliminó correctamente.');
        }

        if (Storage::disk('local')->exists($evidence->path) && ! Storage::disk('local')->delete($evidence->path)) {
            return to_route('modules.cases.evidence.index', ['case' => $case->folio])
                ->withErrors(['evidence' => 'No se pudo eliminar el archivo. La evidencia sigue registrada.']);
        }

        $evidence->delete();

        return to_route('modules.cases.evidence.index', ['case' => $case->folio])
            ->with('demo_status', 'La evidencia se eliminó correctamente.');
    }

    private function nextFolio(): string
    {
        $prefix = 'GIATEP-'.now()->year.'-';
        $folioColumn = OriginalGiatepSchema::isActive() ? 'Folio' : 'folio';
        $last = OccupationalCase::query()->where($folioColumn, 'like', $prefix.'%')->lockForUpdate()->max($folioColumn);
        $sequence = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function storeOriginalCase(StoreOccupationalCaseRequest $request): OccupationalCase
    {
        return DB::transaction(function () use ($request): OccupationalCase {
            $data = $request->validated();
            $user = $request->user();
            $commune = DB::table('comuna')->where('Nombre_Comuna', $data['employer_commune'])->value('Id_Comuna');
            abort_unless($commune, 422, 'La comuna del empleador no existe en COMUNA.');

            [$firstName, $lastName] = $this->splitName($data['worker_name']);
            $workerCommune = DB::table('comuna')->where('Nombre_Comuna', $data['worker_commune'] ?? $data['employer_commune'])->value('Id_Comuna') ?? $commune;
            $employer = Employer::query()->create([
                'Nombre_Empresa' => $data['employer_name'], 'RUT_Empresa' => $data['employer_rut'],
                'Centro_Trabajo' => $data['workplace'], 'Organismo_Administrador' => $data['administrator'],
                'Direccion_Sucursal' => $data['employer_address'], 'Telefono' => $data['employer_phone'] ?? null,
                'Id_Comuna' => $commune, 'Tipo_Empresa' => $data['company_type'],
                'Actividad_Economica' => $data['economic_activity'],
            ]);

            $indigenousId = filled($data['indigenous_people'] ?? null)
                ? DB::table('pueblos_originarios')->where('Nombre_Pueblo', $data['indigenous_people'])->value('Id_Pueblo_Originario')
                : null;
            $worker = Worker::query()->updateOrCreate(['RUT_CI_Trabajador' => $data['worker_rut']], [
                'Nombre' => $firstName, 'Apellido' => $lastName, 'Fecha_Nacimiento' => $data['birth_date'] ?? null,
                'Sexo' => $data['worker_sex'] ?? null, 'Nacionalidad' => $data['nationality'] ?? null,
                'Profesion_U_Oficio' => $data['job_title'], 'Fecha_Ingreso' => $data['hire_date'] ?? null,
                'Antiguedad_En_El_Puesto' => $data['company_tenure'] ?? null,
                'Fecha_Contrato' => $data['hire_date'] ?? null, 'Categoria_Ocupacional' => $data['occupational_category'] ?? null,
                'Direccion_Domicilio' => $data['worker_address'] ?? null, 'Telefono_Personal' => $data['phone'] ?? null,
                'Id_Comuna' => $workerCommune, 'Id_Pueblo_Originario' => $indigenousId,
                'Tipo_Contrato' => $data['contract_type'] ?? null, 'Tipo_Ingreso' => $data['income_type'] ?? null,
                'Jornada_Laboral' => $data['work_schedule'] ?? null,
            ]);

            $typeId = DB::table('tipo_caso')->where('Nombre_Tipo_Caso', $data['case_type'])->value('Id_Clasificacion_Caso');
            abort_unless($typeId, 422, 'El tipo de caso no existe en TIPO_CASO.');
            $folio = $this->nextFolio();
            $caseId = DB::table('caso')->insertGetId([
                'Folio' => $folio, 'Estado' => 'Registrado', 'RUT_CI_Trabajador' => $worker->getKey(),
                'Id_Dependencia' => $data['establishment_id'], 'Id_Clasificacion_Caso' => $typeId,
                'Clasificacion_Caso' => str_contains(strtolower($data['case_type']), 'enfermedad') ? 'DIEP' : 'DIAT',
                'RUT_Usuario' => $user->getKey(), 'Id_Empresa' => $employer->getKey(),
            ], 'Id_Caso');

            $details = $request->all();
            if (str_contains(strtolower($data['case_type']), 'enfermedad')) {
                DB::table('detalle_enfermedad')->insert([
                    'Id_Caso' => $caseId, 'Descripcion_Sintomas' => $data['narrative'],
                    'Duracion_Sintomas_Unidad' => null, 'Molestias_Previas' => $this->yesNo($details['previous_symptoms'] ?? null),
                    'Actividad_Inicio_Sintomas' => $details['activity_when_symptoms_began'] ?? null,
                    'Puesto_Inicio_Sintomas' => $details['symptom_job_position'] ?? null,
                    'Companero_Mismos_Sintomas' => $this->yesNo($details['coworkers_same_symptoms'] ?? null),
                    'Agentes_Sospechados' => $details['suspected_work_agents'] ?? null,
                ]);
            } else {
                DB::table('detalle_accidente')->insert([
                    'Id_Caso' => $caseId, 'Fecha_Accidente' => $data['event_date'],
                    'Hora_Accidente' => $data['event_time'] ?? null, 'Hora_Ingreso_Trabajo' => $details['work_start_time'] ?? null,
                    'Hora_Salida_Trabajo' => $details['work_end_time'] ?? null, 'Direccion_Accidente' => $data['event_location'] ?? null,
                    'Lugar_Accidente' => $data['event_location'] ?? null, 'Actividad_Previa_Accidente' => $details['activity_before_event'] ?? null,
                    'Descripcion_Accidente' => $data['narrative'], 'Trabajo_Habitual' => $details['usual_job_description'] ?? null,
                    'Realizaba_Trabajo_Habitual' => $this->yesNo($details['doing_usual_job'] ?? null),
                    'Clasificacion_Gravedad' => $details['accident_severity'] ?? null,
                    'Tipo_Accidente' => $data['case_type'], 'Tipo_Trayecto' => $details['route_type'] ?? null,
                ]);
            }

            DB::table('relato_cuestionario')->insert(['Id_Caso' => $caseId, 'Relato' => $data['narrative']]);

            foreach ($request->file('evidence_files', []) as $file) {
                DB::table('evidencia')->insert([
                    'Id_Caso' => $caseId, 'Nombre_Archivo' => $file->getClientOriginalName(),
                    'Tipo_MIME' => $file->getMimeType(), 'Archivo' => file_get_contents($file->getRealPath()),
                    'Fecha_Carga' => now(),
                ]);
            }

            return OccupationalCase::query()->where('Id_Caso', $caseId)->firstOrFail();
        });
    }

    /** @param array<string, mixed> $data */
    private function updateOriginalCase(array $data, OccupationalCase $case): void
    {
        DB::transaction(function () use ($data, $case): void {
            $communeId = DB::table('comuna')->where('Nombre_Comuna', $data['employer_commune'])->value('Id_Comuna');
            abort_unless($communeId, 422, 'La comuna del empleador no existe en COMUNA.');
            $workerCommuneId = DB::table('comuna')->where('Nombre_Comuna', $data['worker_commune'] ?? $data['employer_commune'])->value('Id_Comuna') ?? $communeId;
            $typeId = DB::table('tipo_caso')->where('Nombre_Tipo_Caso', $data['case_type'])->value('Id_Clasificacion_Caso');
            abort_unless($typeId, 422, 'El tipo de caso no existe en TIPO_CASO.');

            [$firstName, $lastName] = $this->splitName($data['worker_name']);
            $indigenousId = filled($data['indigenous_people'] ?? null)
                ? DB::table('pueblos_originarios')->where('Nombre_Pueblo', $data['indigenous_people'])->value('Id_Pueblo_Originario')
                : null;
            Worker::query()->updateOrCreate(['RUT_CI_Trabajador' => $data['worker_rut']], [
                'Nombre' => $firstName, 'Apellido' => $lastName, 'Fecha_Nacimiento' => $data['birth_date'] ?? null,
                'Sexo' => $data['worker_sex'] ?? null, 'Nacionalidad' => $data['nationality'] ?? null,
                'Profesion_U_Oficio' => $data['job_title'], 'Fecha_Ingreso' => $data['hire_date'] ?? null,
                'Antiguedad_En_El_Puesto' => $data['company_tenure'] ?? null,
                'Fecha_Contrato' => $data['hire_date'] ?? null, 'Categoria_Ocupacional' => $data['occupational_category'] ?? null,
                'Direccion_Domicilio' => $data['worker_address'] ?? null, 'Telefono_Personal' => $data['phone'] ?? null,
                'Id_Comuna' => $workerCommuneId, 'Id_Pueblo_Originario' => $indigenousId,
                'Tipo_Contrato' => $data['contract_type'] ?? null, 'Tipo_Ingreso' => $data['income_type'] ?? null,
                'Jornada_Laboral' => $data['work_schedule'] ?? null,
            ]);

            DB::table('empresa_empleador')->where('Id_Empresa', $case->Id_Empresa)->update([
                'Nombre_Empresa' => $data['employer_name'], 'RUT_Empresa' => $data['employer_rut'],
                'Centro_Trabajo' => $data['workplace'], 'Organismo_Administrador' => $data['administrator'],
                'Direccion_Sucursal' => $data['employer_address'], 'Telefono' => $data['employer_phone'] ?? null,
                'Id_Comuna' => $communeId, 'Tipo_Empresa' => $data['company_type'],
                'Actividad_Economica' => $data['economic_activity'],
            ]);

            $caseId = $case->getKey();
            $isDisease = str_contains(strtolower($data['case_type']), 'enfermedad');
            DB::table('caso')->where('Id_Caso', $caseId)->update([
                'RUT_CI_Trabajador' => $data['worker_rut'], 'Id_Dependencia' => $data['establishment_id'],
                'Id_Clasificacion_Caso' => $typeId,
                'Clasificacion_Caso' => $isDisease ? 'DIEP' : 'DIAT',
            ]);

            if ($isDisease) {
                DB::table('detalle_accidente')->where('Id_Caso', $caseId)->delete();
                DB::table('detalle_enfermedad')->updateOrInsert(['Id_Caso' => $caseId], [
                    'Descripcion_Sintomas' => $data['narrative'], 'Fecha_Inicio_Sintomas' => $data['event_date'],
                ]);
            } else {
                DB::table('detalle_enfermedad')->where('Id_Caso', $caseId)->delete();
                DB::table('detalle_accidente')->updateOrInsert(['Id_Caso' => $caseId], [
                    'Fecha_Accidente' => $data['event_date'], 'Hora_Accidente' => $data['event_time'] ?? null,
                    'Direccion_Accidente' => $data['event_location'] ?? null, 'Lugar_Accidente' => $data['event_location'] ?? null,
                    'Descripcion_Accidente' => $data['narrative'], 'Tipo_Accidente' => $data['case_type'],
                ]);
            }

            $relatoId = DB::table('relato_cuestionario')->where('Id_Caso', $caseId)->orderByDesc('Id_Relato')->value('Id_Relato');
            if ($relatoId) {
                DB::table('relato_cuestionario')->where('Id_Relato', $relatoId)->update(['Relato' => $data['narrative']]);
            } else {
                DB::table('relato_cuestionario')->insert(['Id_Caso' => $caseId, 'Relato' => $data['narrative']]);
            }
        });
    }

    private function catalogs(): Collection
    {
        if (! OriginalGiatepSchema::isActive()) {
            return CatalogOption::query()->where('is_active', true)->orderBy('sort_order')->get()->groupBy('category');
        }

        $values = [
            'case_type' => DB::table('tipo_caso')->orderBy('Id_Clasificacion_Caso')->pluck('Nombre_Tipo_Caso')->all(),
            'commune' => DB::table('comuna')->orderBy('Nombre_Comuna')->pluck('Nombre_Comuna')->all(),
            'company_type' => ['Principal', 'Contratista', 'Subcontratista', 'Servicios transitorios'],
            'administrator' => ['ACHS', 'IST', 'Mutual de Seguridad', 'ISL'],
            'worker_sex' => ['Mujer', 'Hombre', 'Otro'], 'yes_no' => ['Sí', 'No'],
            'nationality' => ['Chilena', 'Argentina', 'Boliviana', 'Brasileña', 'Colombiana', 'Ecuatoriana', 'Paraguaya', 'Peruana', 'Uruguaya', 'Venezolana', 'Otra nacionalidad'],
            'indigenous_people' => DB::table('pueblos_originarios')->orderBy('Nombre_Pueblo')->pluck('Nombre_Pueblo')->all(),
            'contract_type' => ['Indefinido', 'Plazo fijo', 'Honorarios', 'Contrata', 'Obra o faena', 'Reemplazo'],
            'income_type' => ['Remuneración fija', 'Remuneración variable', 'Honorarios'],
            'occupational_category' => ['Trabajador dependiente', 'Trabajador independiente', 'Familiar no remunerado', 'Trabajador voluntario'],
            'work_schedule' => ['Diurna', 'Turnos rotativos', 'Nocturna', 'Otra'],
            'route_type' => ['Domicilio - Trabajo', 'Trabajo - Domicilio', 'Entre dos trabajos'],
            'proof_type' => ['Parte de Carabineros', 'Declaración', 'Testigos', 'Fotografías', 'Otro'],
        ];

        return collect($values)->map(fn (array $items) => collect($items)->values()->map(fn (string $value, int $index) => (object) ['value' => $value, 'label' => $value, 'sort_order' => $index + 1]));
    }

    /** @return array{string, string} */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [$name];

        return [$parts[0], $parts[1] ?? ''];
    }

    private function yesNo(?string $value): ?bool
    {
        return match ($value) {
            'Sí' => true, 'No' => false, default => null,
        };
    }
}
