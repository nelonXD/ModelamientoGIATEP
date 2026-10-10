<?php

namespace App\Http\Controllers\Modules\Investigations;

use App\Http\Controllers\Modules\ModuleController;
use App\Support\DemoWorkspace;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestigationController extends ModuleController
{
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
            $case = $demo['cases']->firstWhere('id', $caseFolio) ?? $this->caseFromModule($caseFolio) ?? $this->browserCase($caseFolio);
            abort_unless($case, 404);

            $requestedCaseType = $request->string('case_type')->toString();
            if (in_array($requestedCaseType, $this->caseTypes(), true)) {
                $case['type'] = $requestedCaseType;
            }

            if (! $demo['cases']->contains('id', $caseFolio)) {
                $demo['cases']->push($case);
            }

            $selected = $demo['investigations']->firstWhere('case_id', $caseFolio) ?? [
                'id' => 'INV-NUEVA',
                'case_id' => $caseFolio,
                'status' => 'Borrador',
                'progress' => 10,
                'owner' => $request->user()->name,
                'updated' => now()->toDateString(),
                'kind' => 'Investigación',
                'pending' => 'Completar antecedentes y entrevistas',
            ];
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
        return collect([
            ['id' => 'GIATEP-2026-001', 'type' => 'Accidente del trabajo', 'worker' => 'Camila Soto Rojas', 'rut' => '17.456.321-8', 'job_title' => 'Técnica en enfermería', 'establishment' => 'CESFAM Norte', 'date' => '2026-10-02', 'time' => '09:15', 'location' => 'Pasillo de acceso a box clínico', 'status' => 'Borrador', 'qualification' => 'Pendiente', 'injury' => 'Molestia en rodilla derecha', 'body_part' => 'Rodilla derecha', 'story' => 'Durante el traslado de insumos, la persona resbaló en una superficie húmeda.', 'investigation_id' => null, 'employer_name' => 'Dirección Comunal de Salud', 'employer_rut' => '69.170.100-K', 'administrator' => 'ACHS', 'employer_address' => 'Avenida Central 1250, Los Ángeles', 'phone' => '+56 9 5555 0101'],
            ['id' => 'GIATEP-2026-002', 'type' => 'Accidente de trayecto', 'worker' => 'Diego Morales Vera', 'rut' => '18.765.432-1', 'job_title' => 'Administrativo', 'establishment' => 'Dependencia administrativa', 'date' => '2026-10-01', 'time' => '08:05', 'location' => 'Intersección cercana al lugar de trabajo', 'status' => 'En revisión', 'qualification' => 'Pendiente', 'injury' => 'Por confirmar', 'body_part' => 'Por confirmar', 'story' => 'La persona informó una caída durante su trayecto habitual hacia el establecimiento.', 'investigation_id' => null, 'employer_name' => 'Dirección Comunal de Salud', 'employer_rut' => '69.170.100-K', 'administrator' => 'ACHS', 'employer_address' => 'Calle Institucional 450, Los Ángeles', 'phone' => '+56 9 5555 0202'],
        ])->firstWhere('id', $folio);
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

    /** @return array<string, mixed>|null */
    private function browserCase(string $folio): ?array
    {
        if (! str_starts_with($folio, 'GIATEP-DEMO-')) {
            return null;
        }

        return [
            'id' => $folio, 'type' => 'Sin informar', 'worker' => 'Caso guardado en este navegador',
            'rut' => '', 'job_title' => '', 'establishment' => '', 'date' => '', 'time' => '',
            'location' => '', 'status' => 'Borrador', 'qualification' => 'Pendiente', 'injury' => '',
            'body_part' => '', 'story' => '', 'investigation_id' => null, 'employer_name' => '',
            'employer_rut' => '', 'administrator' => '', 'employer_address' => '', 'phone' => '',
        ];
    }
}
