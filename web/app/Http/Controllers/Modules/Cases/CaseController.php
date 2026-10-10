<?php

namespace App\Http\Controllers\Modules\Cases;

use App\Http\Controllers\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaseController extends ModuleController
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPermission('cases.read'), 403);

        $cases = collect([
            [
                'folio' => 'GIATEP-2026-001', 'type' => 'Accidente del trabajo', 'person' => 'Camila Soto Rojas',
                'workerRut' => '17.456.321-8', 'jobTitle' => 'Técnica en enfermería', 'workplace' => 'CESFAM Norte', 'phone' => '+56 9 5555 0101',
                'employerName' => 'Dirección Comunal de Salud', 'employerRut' => '69.170.100-K', 'administrator' => 'ACHS',
                'establishment' => 'CESFAM Norte', 'employerAddress' => 'Avenida Central 1250, Los Ángeles',
                'date' => '02-10-2026', 'time' => '09:15', 'location' => 'Pasillo de acceso a box clínico', 'status' => 'Borrador',
                'summary' => 'Durante el traslado de insumos, la persona resbaló en una superficie húmeda y presentó una molestia en la rodilla derecha.',
                'witnesses' => 'Testigo de demostración y registro fotográfico pendiente.',
                'evidenceFiles' => [
                    ['name' => 'fotografia-lugar.jpg', 'type' => 'image/jpeg', 'size' => 248320],
                    ['name' => 'declaracion-testigo.pdf', 'type' => 'application/pdf', 'size' => 512000],
                ],
            ],
            [
                'folio' => 'GIATEP-2026-002', 'type' => 'Accidente de trayecto', 'person' => 'Diego Morales Vera',
                'workerRut' => '18.765.432-1', 'jobTitle' => 'Administrativo', 'workplace' => 'Dirección comunal', 'phone' => '+56 9 5555 0202',
                'employerName' => 'Dirección Comunal de Salud', 'employerRut' => '69.170.100-K', 'administrator' => 'ACHS',
                'establishment' => 'Dependencia administrativa', 'employerAddress' => 'Calle Institucional 450, Los Ángeles',
                'date' => '01-10-2026', 'time' => '08:05', 'location' => 'Intersección cercana al lugar de trabajo', 'status' => 'En revisión',
                'summary' => 'La persona informó una caída durante su trayecto habitual hacia el establecimiento.',
                'witnesses' => 'Comprobante de atención y croquis de trayecto disponibles para revisión.',
                'evidenceFiles' => [
                    ['name' => 'comprobante-atencion.pdf', 'type' => 'application/pdf', 'size' => 184000],
                ],
            ],
        ]);

        return view('modules.cases.index', compact('cases'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->hasPermission('cases.create'), 403);

        return view('modules.cases.create');
    }
}
