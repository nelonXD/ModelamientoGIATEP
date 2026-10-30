<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Investigation;
use App\Models\OccupationalCase;
use App\Support\DemoWorkspace;
use App\Support\OriginalGiatepSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InvestigationController extends Controller
{
    public function start(Request $request, OccupationalCase $case): RedirectResponse
    {
        abort_unless($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'), 403);
        abort_if(in_array(mb_strtolower($case->case_type), ['accidente de trayecto', 'accidente común', 'enfermedad común', 'accidente o enfermedad común'], true), 422, 'Este tipo de caso no requiere investigación.');

        $investigation = DB::transaction(function () use ($case, $request) {
            $lockedCase = OccupationalCase::query()->whereKey($case->getKey())->lockForUpdate()->firstOrFail();
            $investigation = $lockedCase->investigation()->first();

            if (! $investigation) {
                $key = OriginalGiatepSchema::isActive() ? 'Codigo_Investigacion' : 'id';
                $sequence = (int) Investigation::query()->lockForUpdate()->max($key) + 1;
                $investigation = $lockedCase->investigation()->create([
                    'folio' => 'INV-'.now()->year.'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                    'owner_id' => $request->user()->id,
                    'status' => 'En desarrollo',
                    'final_story' => $lockedCase->narrative,
                    'created_at' => now(),
                ]);
            }

            if (in_array($lockedCase->status, ['Registrado', 'Borrador'], true)) {
                $lockedCase->update(['status' => 'En investigación']);
            }

            return $investigation;
        });

        $caseType = $request->string('case_type')->toString();

        return to_route('modules.investigations.index', array_filter([
            'case' => $case->folio,
            'case_type' => $caseType !== '' ? $caseType : null,
        ]))->with('demo_status', "Investigación {$investigation->folio} iniciada correctamente.");
    }

    public function workflow(Request $request, OccupationalCase $case): RedirectResponse
    {
        $action = $request->string('workflow_action')->toString();
        $user = $request->user();
        $investigation = $case->investigation()->firstOrFail();

        if ($action === 'submit') {
            abort_unless($user->hasRole('cphs') || $user->hasRole('delegado') || $user->hasRole('prevencion'), 403);
            abort_unless($investigation->status === 'En desarrollo', 409);
            $investigation->update(['status' => 'En validación']);
            DB::table('investigation_events')->insert(['investigation_folio' => $investigation->folio, 'actor_rut' => $user->rut, 'actor_name' => $user->name, 'actor_role' => $user->roles->pluck('name')->implode(', '), 'event' => 'Enviada a validación', 'created_at' => now()]);

            return back()->with('demo_status', 'La investigación fue enviada a Prevención para su validación.');
        }

        if ($action === 'close') {
            abort_unless($user->hasRole('prevencion') || $user->hasRole('cphs') || $user->hasRole('delegado'), 403);
            abort_unless($investigation->status === 'En validación', 409);
            $pending = DB::table('investigation_observations')->where('investigation_folio', $investigation->folio)->where('status', 'pendiente')->exists();
            abort_if($pending, 422, 'Responde las observaciones pendientes antes de cerrar la investigación.');
            $investigation->update(['status' => 'Completada']);
            DB::table('investigation_events')->insert(['investigation_folio' => $investigation->folio, 'actor_rut' => $user->rut, 'actor_name' => $user->name, 'actor_role' => $user->roles->pluck('name')->implode(', '), 'event' => 'Investigación cerrada y completada', 'created_at' => now()]);

            return back()->with('demo_status', 'La investigación quedó completada y bloqueada para edición.');
        }

        abort_unless($action === 'save', 422);
        abort_unless($user->hasRole('cphs') || $user->hasRole('delegado') || $user->hasRole('prevencion'), 403);
        abort_unless($investigation->status === 'En desarrollo', 409);
        $payload = collect($request->except(['_token', 'workflow_action', 'return_to']))
            ->map(fn ($value) => is_array($value) ? array_slice($value, 0, 100) : mb_substr((string) $value, 0, 20000))
            ->all();
        DB::table('investigation_drafts')->updateOrInsert(
            ['investigation_folio' => $investigation->folio],
            ['payload' => json_encode($payload, JSON_UNESCAPED_UNICODE), 'updated_by_rut' => $user->rut, 'updated_at' => now(), 'created_at' => now()],
        );
        DB::table('investigation_events')->insert(['investigation_folio' => $investigation->folio, 'actor_rut' => $user->rut, 'actor_name' => $user->name, 'actor_role' => $user->roles->pluck('name')->implode(', '), 'event' => 'Cambios guardados durante el desarrollo', 'created_at' => now()]);

        return back()->with('demo_status', 'Los cambios de la investigación se guardaron. Sigue En desarrollo.');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->hasPermission('investigations.read'), 403);

        $workspace = app(DemoWorkspace::class);
        $caseFolio = $request->string('case')->toString();
        $demo = $workspace->data($caseFolio !== '', $caseFolio !== '');
        $selected = null;
        $leadershipReport = $request->user()->hasRole('jefatura') || $request->user()->hasRole('alta-direccion');
        $reportCase = null;
        $reportInvestigation = null;
        $reportObservations = collect();
        $canReplyObservation = false;
        $investigationDraft = [];

        if ($caseFolio !== '') {
            $case = $demo['cases']->firstWhere('id', $caseFolio) ?? $this->caseFromModule($caseFolio);
            abort_unless($case, 404);

            $requestedCaseType = $request->string('case_type')->toString();
            if (in_array($requestedCaseType, $this->caseTypes(), true)) {
                $case['type'] = $requestedCaseType;
            }

            $demo['cases'] = $demo['cases']->reject(fn (array $item): bool => $item['id'] === $caseFolio)->prepend($case);

            $selected = $demo['investigations']->firstWhere('case_id', $caseFolio);

            if (! $selected) {
                $dbCase = OccupationalCase::where('folio', $caseFolio)->with('investigation.owner')->first();
                if ($dbCase && $dbCase->investigation) {
                    $inv = $dbCase->investigation;
                    $selected = [
                        'id' => $inv->folio,
                        'case_id' => $caseFolio,
                        'case_type' => $dbCase->case_type,
                        'worker' => $dbCase->worker_name,
                        'establishment' => $dbCase->establishment?->name ?? 'Establecimiento de salud',
                        'status' => $inv->status,
                        'progress' => 50,
                        'owner' => $inv->owner?->name ?? $request->user()->name,
                        'updated' => $inv->updated_at->toDateString(),
                        'kind' => 'Árbol de causas',
                        'final_story' => $inv->final_story ?: $dbCase->narrative,
                        'has_facts' => $inv->facts()->exists(),
                        'pending' => 'Completar árbol de causas y validar medidas',
                    ];
                } else {
                    $selected = [
                        'id' => 'INV-NUEVA',
                        'case_id' => $caseFolio,
                        'case_type' => $case['type'],
                        'worker' => $case['worker'],
                        'establishment' => $case['establishment'],
                        'status' => 'Borrador',
                        'progress' => 20,
                        'owner' => $request->user()->name,
                        'updated' => now()->toDateString(),
                        'kind' => 'Árbol de causas',
                        'final_story' => $case['story'] ?? '',
                        'has_facts' => false,
                        'pending' => 'Revisar relato final y ejecutar análisis IA',
                    ];
                }
            }

            if ($leadershipReport) {
                $relations = ['establishment', 'evidences', 'investigation.owner'];
                if (! OriginalGiatepSchema::isActive()) {
                    $relations = [...$relations, 'investigation.facts', 'investigation.causalNodes', 'investigation.measures', 'investigation.interviews.answers', 'investigation.causeFactors'];
                }
                $reportCase = OccupationalCase::query()->with($relations)->where('folio', $caseFolio)->first();
                $reportInvestigation = $reportCase?->investigation;
            }
        }

        if ($selected && ($selected['id'] ?? '') !== 'INV-NUEVA' && Schema::hasTable('investigation_observations')) {
            if (Schema::hasTable('investigation_drafts')) {
                $storedDraft = DB::table('investigation_drafts')->where('investigation_folio', $selected['id'])->value('payload');
                $investigationDraft = is_string($storedDraft) ? (json_decode($storedDraft, true) ?: []) : (array) $storedDraft;
            }
            $observationQuery = DB::table('investigation_observations')->where('investigation_folio', $selected['id'])->whereNull('parent_id');
            if ($leadershipReport) {
                $observationQuery->where('author_rut', $request->user()->rut);
            }
            $reportObservations = $observationQuery->orderBy('created_at')->get();
            foreach ($reportObservations as $observation) {
                $observation->replies = DB::table('investigation_observations')->where('parent_id', $observation->id)->orderBy('created_at')->get();
            }
            $caseInvestigation = OccupationalCase::where('folio', $caseFolio)->with('investigation.owner')->first()?->investigation;
            $canReplyObservation = $caseInvestigation?->owner?->rut === $request->user()->rut
                && ($request->user()->hasRole('cphs') || $request->user()->hasRole('delegado') || $request->user()->hasRole('prevencion'));
        }

        return view('demo.workspace', [
            'module' => 'investigations',
            'selected' => $selected,
            'mode' => $selected ? 'create' : 'index',
            'demo' => $demo,
            'title' => 'Investigaciones',
            'description' => 'Recopilación de antecedentes, evidencias, entrevistas y análisis causal.',
            'leadershipReport' => $leadershipReport,
            'reportCase' => $reportCase,
            'reportInvestigation' => $reportInvestigation,
            'reportObservations' => $reportObservations,
            'canReplyObservation' => $canReplyObservation,
            'investigationDraft' => $investigationDraft,
        ]);
    }

    /** @return array<string, mixed>|null */
    private function caseFromModule(string $folio): ?array
    {
        $storedCase = OccupationalCase::query()->with(['establishment', 'investigation'])->where('folio', $folio)->first();
        if ($storedCase) {
            return [
                'id' => $storedCase->folio, 'type' => $storedCase->case_type, 'worker' => $storedCase->worker_name,
                'rut' => $storedCase->worker_rut, 'job_title' => $storedCase->job_title,
                'establishment' => $storedCase->establishment?->name ?? $storedCase->workplace,
                'date' => $storedCase->event_date->toDateString(), 'time' => $storedCase->event_time,
                'location' => $storedCase->event_location, 'status' => $storedCase->status,
                'qualification' => 'Pendiente', 'injury' => $storedCase->details['affected_body_part'] ?? '',
                'body_part' => $storedCase->details['affected_body_part'] ?? '', 'story' => $storedCase->narrative,
                'investigation_id' => $storedCase->investigation?->folio, 'employer_name' => $storedCase->employer_name,
                'employer_rut' => $storedCase->employer_rut, 'administrator' => $storedCase->administrator,
                'employer_address' => $storedCase->employer_address, 'phone' => $storedCase->worker_phone,
            ];
        }

        return null;
    }

    /** @return list<string> */
    private function caseTypes(): array
    {
        return [
            'Accidente de trabajo', 'Accidente de trayecto', 'Enfermedad profesional', 'Accidente grave',
            'Accidente fatal', 'Accidente de trabajo sin días perdidos', 'Incidente o suceso peligroso',
            'Accidente o enfermedad común',
        ];
    }
}
