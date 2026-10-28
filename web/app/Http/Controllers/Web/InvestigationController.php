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
use Illuminate\View\View;

class InvestigationController extends Controller
{
    public function start(Request $request, OccupationalCase $case): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('investigations.read'), 403);

        $investigation = DB::transaction(function () use ($case, $request) {
            $lockedCase = OccupationalCase::query()->whereKey($case->getKey())->lockForUpdate()->firstOrFail();
            $investigation = $lockedCase->investigation()->first();

            if (! $investigation) {
                $key = OriginalGiatepSchema::isActive() ? 'Codigo_Investigacion' : 'id';
                $sequence = (int) Investigation::query()->lockForUpdate()->max($key) + 1;
                $investigation = $lockedCase->investigation()->create([
                    'folio' => 'INV-'.now()->year.'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                    'owner_id' => $request->user()->id,
                    'status' => 'En investigación',
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

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->hasPermission('investigations.read'), 403);

        $workspace = app(DemoWorkspace::class);
        $demo = $workspace->data();
        $caseFolio = $request->string('case')->toString();
        $selected = null;

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
                        'has_facts' => ! OriginalGiatepSchema::isActive() && $inv->facts()->exists(),
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
        }

        return view('demo.workspace', [
            'module' => 'investigations',
            'selected' => $selected,
            'mode' => $selected ? 'create' : 'index',
            'demo' => $demo,
            'title' => 'Investigaciones',
            'description' => 'Recopilación de antecedentes, evidencias, entrevistas y análisis causal.',
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
