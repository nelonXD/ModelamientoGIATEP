<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PendingModuleController extends Controller
{
    public function __invoke(Request $request, string $module): View
    {
        $modules = [
            'casos' => ['Casos', 'cases.read'],
            'investigaciones' => ['Investigaciones', 'investigations.read'],
            'medidas' => ['Medidas y planes de acción', 'measures.read'],
            'observaciones' => ['Observaciones', 'observations.read'],
            'revisiones' => ['Revisiones y validaciones', 'reviews.read'],
            'estadisticas' => ['Dashboards y estadísticas', 'statistics.read'],
            'reportes' => ['Reportes', 'reports.export'],
            'perfil' => ['Mi perfil', 'profile.read'],
            'usuarios' => ['Usuarios', 'users.manage'],
            'roles' => ['Roles y permisos', 'roles.manage'],
            'establecimientos' => ['Establecimientos', 'establishments.manage'],
            'parametros' => ['Parámetros institucionales', 'settings.manage'],
        ];

        abort_unless(isset($modules[$module]), 404);
        abort_unless($request->user()->hasPermission($modules[$module][1]), 403);

        return view('pending-module', ['title' => $modules[$module][0]]);
    }
}
