<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['roles', 'establishments']);

        $availableActions = collect([
            ['permission' => 'cases.read', 'title' => 'Casos', 'description' => 'Registra y consulta casos dentro de tus establecimientos autorizados.', 'module' => 'casos'],
            ['permission' => 'investigations.read', 'title' => 'Investigaciones', 'description' => 'Consulta investigaciones y sus antecedentes disponibles.', 'module' => 'investigaciones'],
            ['permission' => 'reviews.read', 'title' => 'Revisiones y validaciones', 'description' => 'Revisa antecedentes, medidas y planes antes de su validación.', 'module' => 'revisiones'],
            ['permission' => 'observations.read', 'title' => 'Observaciones', 'description' => 'Registra y consulta observaciones asociadas al seguimiento.', 'module' => 'observaciones'],
            ['permission' => 'measures.read', 'title' => 'Medidas y planes de acción', 'description' => 'Consulta las medidas de control y sus planes de acción.', 'module' => 'medidas'],
            ['permission' => 'statistics.read', 'title' => 'Dashboards y estadísticas', 'description' => 'Accede a indicadores consolidados según tu alcance autorizado.', 'module' => 'estadisticas'],
            ['permission' => 'reports.export', 'title' => 'Reportes', 'description' => 'Consulta y exporta reportes habilitados para tu rol.', 'module' => 'reportes'],
        ])->filter(fn (array $action): bool => $user->hasPermission($action['permission']))->values();

        $roleSlugs = $user->roles->pluck('slug');
        $dashboardProfile = match (true) {
            $roleSlugs->contains('prevencion') => [
                'label' => 'Prevencionista',
                'message' => 'Gestiona casos, revisiones, medidas preventivas e información consolidada dentro de tu alcance.',
            ],
            $roleSlugs->contains('alta-direccion') => [
                'label' => 'Alta Dirección',
                'message' => 'Consulta investigaciones, seguimiento preventivo e indicadores para apoyar la toma de decisiones.',
            ],
            $roleSlugs->contains('jefatura') => [
                'label' => 'Jefatura',
                'message' => 'Consulta investigaciones y participa en el seguimiento de observaciones y medidas de control.',
            ],
            default => [
                'label' => 'Panel de inicio',
                'message' => 'Accede a las funciones habilitadas para tus roles y establecimientos autorizados.',
            ],
        };

        return view('dashboard', compact('user', 'availableActions', 'dashboardProfile'));
    }
}
